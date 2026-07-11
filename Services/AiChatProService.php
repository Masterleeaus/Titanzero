<?php

namespace Modules\TitanZero\Services;

use Illuminate\Support\Facades\Log;
use JsonException;

/**
 * AiChatProService for TitanZero.
 *
 * Provides tool-calling definitions for the AIChatPro interface.
 * Image generation is routed through ZeroGateway instead of direct provider calls.
 */
class AiChatProService
{
    private static function availableTools(): array
    {
        return [
            [
                'type'        => 'function',
                'name'        => 'generate_image',
                'description' => 'Generate an image based on the given prompt.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'prompt' => [
                            'type'        => 'string',
                            'description' => 'The text prompt to generate the image.',
                        ],
                    ],
                    'required' => ['prompt'],
                ],
            ],
        ];
    }

    public static function tools(): array
    {
        return self::availableTools();
    }

    public static function callFunction(?string $functionName, ?string $argsString): ?string
    {
        return match ($functionName) {
            'generate_image' => self::generateImageViaGateway($argsString),
            default          => null,
        };
    }

    /**
     * Route image generation through ZeroGateway signal instead of a direct API call.
     */
    public static function generateImageViaGateway(?string $argsString): string
    {
        try {
            $args = json_decode($argsString ?? '{}', true, 512, JSON_THROW_ON_ERROR);
            if (empty($args['prompt'])) {
                throw new JsonException('Invalid arguments: prompt is required');
            }

            /** @var ZeroGateway|null $gateway */
            $gateway = app()->bound(ZeroGateway::class)
                ? app(ZeroGateway::class)
                : null;

            if ($gateway === null) {
                return json_encode(['error' => 'ZeroGateway not available'], JSON_THROW_ON_ERROR);
            }

            $result = $gateway->ingestSignal([
                'type'    => 'generate_image',
                'payload' => ['prompt' => $args['prompt']],
            ]);

            return json_encode($result, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            Log::error('[TitanZero][AiChatProService] ' . $e->getMessage());

            return json_encode(['error' => $e->getMessage()]);
        }
    }
}
