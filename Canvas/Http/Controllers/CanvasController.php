<?php

namespace Modules\TitanZero\Canvas\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanZero\Entities\AiChatMessage;
use Throwable;

class CanvasController extends Controller
{
    /**
     * Store or update TipTap content (input or output) for a chat message.
     */
    public function storeContent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content'    => 'nullable|string',
            'type'       => 'required|string|in:input,output',
            'message_id' => 'required|integer',
        ]);

        try {
            $message = AiChatMessage::findOrFail((int) $validated['message_id']);

            if ($message->user_id !== auth()->id()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
            }

            $fields = ['user_id' => auth()->id()];

            if ($validated['type'] === 'input') {
                $fields['input'] = $validated['content'];
            } else {
                $fields['output'] = $validated['content'];
            }

            $message->tiptapContent()->updateOrCreate(
                ['save_contentable_id' => $message->id, 'save_contentable_type' => AiChatMessage::class],
                $fields
            );

            return response()->json(['status' => 'success']);
        } catch (Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Save or update the document title for a chat message's Canvas content.
     */
    public function saveTitle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message_id' => 'required|integer',
            'title'      => 'sometimes|nullable|string|max:255',
        ]);

        try {
            $message = AiChatMessage::findOrFail((int) $validated['message_id']);

            if ($message->user_id !== auth()->id()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
            }

            $message->tiptapContent()->updateOrCreate(
                ['save_contentable_id' => $message->id, 'save_contentable_type' => AiChatMessage::class],
                [
                    'title'   => $validated['title'] ?? null,
                    'user_id' => auth()->id(),
                ]
            );

            return response()->json(['status' => 'success']);
        } catch (Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
