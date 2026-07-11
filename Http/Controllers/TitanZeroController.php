<?php

namespace Modules\TitanZero\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Http\Controllers\TitanZero\SuggestionsController as TitanZeroSuggestionsController;
use App\Models\TitanZeroThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\TitanZero\Services\TitanZeroService;

class TitanZeroController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('titanzero::app.menu.titanZero') ?? 'Titan Zero';
    }

    public function index()
    {
        return view('titanzero::pages.dashboard');
    }

    public function help()
    {
        return view('titanzero::pages.help');
    }

    public function chat()
    {
        return redirect()->route('titan.zero.ai-chat.index');
    }

    public function generators()
    {
        return view('titanzero::pages.generators', [
            'items' => config('titanzero.generators', []),
        ]);
    }

    public function templates()
    {
        return view('titanzero::pages.templates', [
            'items' => config('titanzero.templates', []),
        ]);
    }


    public function settings()
    {
        return view('titanzero::pages.settings', [
            'features' => config('titanzero.cleaning_features', []),
        ]);
    }

    public function saveSettings(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'enabled_features' => ['nullable', 'array'],
        ]);

        // Store per-company settings (simple JSON blob in app settings or cache)
        $features = $request->input('enabled_features', []);
        \Illuminate\Support\Facades\Cache::put(
            'titanzero.features.' . (auth()->user()->company_id ?? 0),
            $features,
            now()->addDays(30)
        );

        return back()->with('success', __('TitanZero settings saved.'));
    }

    public function ping()
    {
        return response()->json(['status' => 'ok', 'module' => 'titanzero', 'pass' => 3]);
    }

    public function generateUi(Request $request, TitanZeroService $service): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'threadId' => ['nullable', 'integer', 'min:1'],
            'thread_id' => ['nullable', 'integer', 'min:1'],
            'context' => ['nullable', 'array'],
            'context.appKey' => ['nullable', 'string', 'max:80'],
            'context.app_key' => ['nullable', 'string', 'max:80'],
            'context.page' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $message = trim((string) $validated['message']);
        $context = $validated['context'] ?? [];
        $threadId = $this->resolveThreadId($validated);
        $appKey = (string) ($context['appKey'] ?? $context['app_key'] ?? 'default');
        $organizationId = $user?->organization_id ?? $user?->company_id;

        $thread = null;

        if ($threadId !== null) {
            $thread = TitanZeroThread::query()
                ->whereKey($threadId)
                ->where('user_id', $user?->id)
                ->first();
        }

        if (! $thread) {
            $thread = new TitanZeroThread([
                'organization_id' => $organizationId,
                'user_id' => $user?->id,
                'app_key' => $appKey,
                'messages' => [],
                'widgets' => [],
            ]);
        }

        if (! $thread->title) {
            $thread->title = Str::limit($message, 60);
        }

        $history = $thread->messages ?? [];

        $thread->appendMessage([
            'id' => (string) Str::uuid(),
            'role' => 'user',
            'content' => $message,
            'createdAt' => now()->toISOString(),
        ]);

        $response = $service->respond($message, $history, array_merge($context, [
            'organization_id' => $organizationId,
        ]));

        $thread->appendMessage([
            'id' => (string) Str::uuid(),
            'role' => 'assistant',
            'content' => $response['message'],
            'createdAt' => now()->toISOString(),
            'widgets' => $response['parts'],
        ]);

        $thread->widgets = $response['parts'];
        $thread->save();

        $threadKey = (string) $thread->getKey();
        $suggestions = TitanZeroSuggestionsController::suggestionsForAppKey($appKey);

        return response()->json([
            'is_task_complete' => true,
            'message' => $response['message'],
            'reply' => $response['reply'],
            'parts' => $response['parts'],
            'widgets' => $response['widgets'],
            'errors' => [],
            'thread' => $threadKey,
            'meta' => [
                'threadId' => $threadKey,
                'suggestions' => $suggestions,
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveThreadId(array $validated): ?int
    {
        $threadId = $validated['thread_id'] ?? $validated['threadId'] ?? null;

        return is_int($threadId) ? $threadId : null;
    }
}
