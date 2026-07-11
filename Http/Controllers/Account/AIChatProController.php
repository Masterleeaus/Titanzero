<?php

namespace Modules\TitanZero\Http\Controllers\Account;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\TitanZero\Entities\AiChatCategory;
use Modules\TitanZero\Entities\AiChatMessage;
use Modules\TitanZero\Entities\AiChatSession;
use Modules\TitanZero\Services\AiChatProService;
use Modules\TitanZero\Services\AIFileChatService;
use Modules\TitanZero\Services\ZeroGateway;

/**
 * AIChatPro controller for TitanZero.
 *
 * Adapted from AIChatPro v2.5.0. All AI generation routes through ZeroGateway
 * instead of direct OpenAI / Bedrock provider calls.
 */
class AIChatProController extends Controller
{
    public function __construct(protected ZeroGateway $gateway)
    {
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Main page
    // ─────────────────────────────────────────────────────────────────────────

    public function index(string $slug = null)
    {
        if (! Auth::check()) {
            return $this->guestIndex();
        }

        $category = $this->resolveCategory($slug);

        if (! $category) {
            abort(404, 'No AI chat category found.');
        }

        $defaultScreen = config('titanzero.aichatpro.default_screen', 'new');

        $query = $this->scopedSessionQuery()
            ->where('category_id', $category->id)
            ->where('is_chatbot', false);

        switch ($defaultScreen) {
            case 'pinned':
                $list = $query->orderBy('is_pinned', 'desc')
                    ->orderBy('updated_at', 'desc')
                    ->get();
                $chat = $list->first(fn ($c) => $c->is_pinned) ?? $list->first();
                break;

            case 'new':
                $chat = $this->createNewSession($category);
                $list = $query->orderBy('is_pinned', 'desc')
                    ->orderBy('updated_at', 'desc')
                    ->get();
                break;

            case 'last':
            default:
                $list  = $query->orderBy('updated_at', 'desc')->get();
                $chat  = $list->first();
                break;
        }

        $categories      = AiChatCategory::where('is_enabled', true)->get();
        $lastMessages    = null;
        $chatCompletions = null;

        if ($chat !== null) {
            $lastMessages = $chat->messages()
                ->whereNotNull('input')
                ->orderBy('created_at', 'desc')
                ->take(2)
                ->get()
                ->reverse()
                ->values();

            $chatCompletions = $category->chat_completions
                ? json_decode($category->chat_completions, true)
                : null;
        }

        $tools = AiChatProService::tools();

        return view('titanzero::pages.ai-chat', compact(
            'category',
            'categories',
            'list',
            'chat',
            'lastMessages',
            'chatCompletions',
            'tools',
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Send a message (POST)
    // ─────────────────────────────────────────────────────────────────────────

    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message'    => ['required', 'string', 'max:4000'],
            'session_id' => ['required', 'integer'],
        ]);

        $session = AiChatSession::findOrFail((int) $request->input('session_id'));

        // Authorise: owner or guest session
        if (! $session->is_guest && $session->user_id !== Auth::id()) {
            abort(403);
        }

        $userInput = trim((string) $request->input('message'));
        $category  = $session->category;

        // Build KB collection key from category slug
        $kbKey = $this->kbKeyFromCategory($category);

        // Build history context for the gateway
        $history = $session->messages()
            ->whereNotNull('input')
            ->orderBy('created_at', 'asc')
            ->take(6)
            ->get()
            ->map(fn ($m) => [
                ['role' => 'user',      'content' => $m->input  ?? ''],
                ['role' => 'assistant', 'content' => $m->output ?? ''],
            ])
            ->flatten(1)
            ->values()
            ->toArray();

        $envelope = [
            'query'              => $userInput,
            'kb_collection_key'  => $kbKey,
            'history'            => $history,
            'category_slug'      => $category?->slug ?? 'tz-general-assistant',
            // AiChatProFileChat v1.1.0 — pass vector store context when available
            'vector_store_id'    => $session->openai_vector_id ?: null,
            'openai_file_id'     => $session->openai_file_id   ?: null,
            'doc_name'           => $session->doc_name         ?: null,
        ];

        $result = $this->gateway->proposeDocument($envelope, $this->tenantId());

        $reply  = $result['draft_text'] ?? '';
        $words  = str_word_count($reply);

        // Persist message
        $message = new AiChatMessage([
            'ai_chat_session_id' => $session->id,
            'user_id'            => Auth::id(),
            'input'              => $userInput,
            'output'             => $reply,
            'response'           => 'ok',
            'hash'               => Str::random(64),
            'credits'            => 0,
            'words'              => $words,
        ]);
        $message->save();

        // Update session totals
        $session->increment('total_words', $words);

        return response()->json([
            'ok'         => true,
            'message_id' => $message->id,
            'reply'      => $reply,
            'citations'  => $result['citations'] ?? [],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Start a new session (POST)
    // ─────────────────────────────────────────────────────────────────────────

    public function startNewSession(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => ['required', 'integer'],
        ]);

        $category = AiChatCategory::findOrFail((int) $request->input('category_id'));
        $session  = $this->createNewSession($category);

        return response()->json([
            'ok'         => true,
            'session_id' => $session->id,
            'title'      => $session->title,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  File upload (POST) — AiChatProFileChat v1.1.0
    // ─────────────────────────────────────────────────────────────────────────

    public function uploadFile(Request $request): JsonResponse
    {
        if (! config('titanzero.file_chat.allowed', true)) {
            return response()->json(['ok' => false, 'message' => __('File chat is not enabled.')], 403);
        }

        $request->validate([
            'session_id' => ['required', 'integer'],
            'file'       => ['required', 'file', 'mimes:pdf,docx,txt,doc,csv,xlsx', 'max:20480'],
        ]);

        $session = AiChatSession::findOrFail((int) $request->input('session_id'));

        if (! $session->is_guest && $session->user_id !== Auth::id()) {
            abort(403);
        }

        $uploadedFile = $request->file('file');
        $fileName     = $uploadedFile->getClientOriginalName();
        $relativePath = $uploadedFile->store('ai-chat-files', 'public');

        $service = new AIFileChatService(
            filePaths: storage_path('app/public/' . $relativePath),
            sessionId: (string) $session->id
        );

        $ok = $service->validateAndAnalyzeFile();

        if (! $ok) {
            return response()->json([
                'ok'      => false,
                'message' => __('File could not be processed. Please check your OpenAI API key configuration.'),
            ], 422);
        }

        $session->refresh();

        return response()->json([
            'ok'               => true,
            'doc_name'         => $session->doc_name ?? $fileName,
            'reference_url'    => $session->reference_url,
            'openai_file_id'   => $session->openai_file_id,
            'openai_vector_id' => $session->openai_vector_id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Guest chat (unauthenticated)
    // ─────────────────────────────────────────────────────────────────────────

    protected function guestIndex()
    {
        $this->purgeStaleGuestSessions();

        $category = AiChatCategory::where('is_enabled', true)
            ->where('role', 'default')
            ->first()
            ?? AiChatCategory::where('is_enabled', true)->first();

        if (! $category) {
            return view('titanzero::pages.ai-chat-unavailable');
        }

        $chat = $this->createGuestSession($category);
        $list = [$chat];

        return view('titanzero::pages.ai-chat', [
            'category'       => $category,
            'categories'     => collect([$category]),
            'list'           => collect($list),
            'chat'           => $chat,
            'lastMessages'   => collect(),
            'chatCompletions'=> null,
            'tools'          => AiChatProService::tools(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────────────────

    protected function resolveCategory(?string $slug): ?AiChatCategory
    {
        if ($slug) {
            return AiChatCategory::where('slug', $slug)->where('is_enabled', true)->first();
        }

        return AiChatCategory::where('is_enabled', true)
            ->where('role', 'default')
            ->first()
            ?? AiChatCategory::where('is_enabled', true)->first();
    }

    protected function createNewSession(AiChatCategory $category): AiChatSession
    {
        $this->purgeEmptySessionsForUser($category);

        $session = new AiChatSession([
            'user_id'      => Auth::id(),
            'company_id'   => $this->tenantId(),
            'category_id'  => $category->id,
            'title'        => $category->name . ' Chat',
            'total_credits'=> 0,
            'total_words'  => 0,
            'is_guest'     => false,
            'is_pinned'    => false,
            'is_chatbot'   => false,
        ]);
        $session->save();
        $session->refresh();

        $greeting = $this->buildGreeting($category);

        $initMessage = new AiChatMessage([
            'ai_chat_session_id' => $session->id,
            'user_id'            => Auth::id(),
            'input'              => null,
            'output'             => $greeting,
            'response'           => 'First Initiation',
            'hash'               => Str::random(64),
            'credits'            => 0,
            'words'              => 0,
        ]);
        $initMessage->save();

        return $session;
    }

    protected function createGuestSession(AiChatCategory $category): AiChatSession
    {
        $session = new AiChatSession([
            'user_id'      => null,
            'company_id'   => null,
            'category_id'  => $category->id,
            'title'        => $category->name . ' Chat',
            'total_credits'=> 0,
            'total_words'  => 0,
            'is_guest'     => true,
            'is_pinned'    => false,
            'is_chatbot'   => false,
        ]);
        $session->save();

        return $session;
    }

    protected function purgeStaleGuestSessions(): void
    {
        $stale = AiChatSession::where('is_guest', true)
            ->where('created_at', '<', now()->subDay())
            ->get();

        foreach ($stale as $session) {
            $session->messages()->delete();
            $session->delete();
        }
    }

    protected function purgeEmptySessionsForUser(AiChatCategory $category): void
    {
        $this->scopedSessionQuery()
            ->where('category_id', $category->id)
            ->whereDoesntHave('messages', fn ($q) => $q->whereNotNull('input'))
            ->delete();
    }

    protected function scopedSessionQuery()
    {
        return AiChatSession::query()->where('user_id', Auth::id());
    }

    protected function buildGreeting(AiChatCategory $category): string
    {
        $name = $category->human_name ?? $category->name;

        if ($category->role === 'default') {
            return __("Hi! I am :name, and I'm here to answer all your questions", ['name' => $name]);
        }

        return __("Hi! I am :name, and I'm :role. :helps", [
            'name'  => $name,
            'role'  => $category->role,
            'helps' => $category->helps_with ?? '',
        ]);
    }

    protected function kbKeyFromCategory(?AiChatCategory $category): string
    {
        $map = [
            'tz-project-assistant' => 'kb_project_management',
            'tz-invoice-drafter'   => 'kb_finance',
            'tz-hr-policy-bot'     => 'kb_hr_policy',
        ];

        return $map[$category?->slug ?? ''] ?? 'kb_general_cleaning';
    }

    protected function tenantId(): ?int
    {
        return Auth::check() ? (auth()->user()->company_id ?? null) : null;
    }
}
