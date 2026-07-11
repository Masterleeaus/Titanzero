<?php

namespace Modules\TitanZero\Services;

use Illuminate\Support\Str;

class WidgetFactory
{
    /**
     * @var list<string>
     */
    private const VALID_KINDS = [
        'metric-card',
        'line-chart',
        'bar-chart',
        'data-table',
        'log-list',
        'chat-thread',
        'tool-call',
        'settings-form',
        'mcp-server-list',
        'project-list',
    ];

    /**
     * @param  mixed  $parts
     * @return list<array<string, mixed>>
     */
    public function fromResponse(string $intent, string $reply, mixed $parts = []): array
    {
        $widgets = $this->normalize($parts, $intent);

        if ($widgets !== []) {
            return $widgets;
        }

        return [$this->makeFromIntent($intent, $reply)];
    }

    /**
     * @return array<string, mixed>
     */
    public function makeFromIntent(string $intent, ?string $reply = null): array
    {
        $kind = $this->detectKind($intent);
        $title = $this->titleFromIntent($intent);
        $summary = Str::limit($reply ?: $intent, 120);

        return match ($kind) {
            'data-table' => [
                'id' => $this->widgetId('table'),
                'kind' => 'data-table',
                'title' => $title,
                'data' => [
                    'columns' => [
                        ['key' => 'item', 'label' => 'Item'],
                        ['key' => 'summary', 'label' => 'Summary'],
                    ],
                    'rows' => [
                        ['item' => $title, 'summary' => $summary],
                    ],
                ],
            ],
            'line-chart' => [
                'id' => $this->widgetId('line'),
                'kind' => 'line-chart',
                'title' => $title,
                'data' => [
                    'labels' => ['Current'],
                    'datasets' => [
                        ['label' => $title, 'data' => [1]],
                    ],
                ],
            ],
            'bar-chart' => [
                'id' => $this->widgetId('bar'),
                'kind' => 'bar-chart',
                'title' => $title,
                'data' => [
                    'labels' => ['Current'],
                    'datasets' => [
                        ['label' => $title, 'data' => [1]],
                    ],
                ],
            ],
            'log-list' => [
                'id' => $this->widgetId('log'),
                'kind' => 'log-list',
                'title' => $title,
                'data' => [
                    'entries' => [
                        ['level' => 'info', 'message' => $summary],
                    ],
                ],
            ],
            'chat-thread' => [
                'id' => $this->widgetId('chat'),
                'kind' => 'chat-thread',
                'title' => $title,
                'data' => [
                    'messages' => [
                        ['role' => 'assistant', 'content' => $summary],
                    ],
                ],
            ],
            'tool-call' => [
                'id' => $this->widgetId('tool'),
                'kind' => 'tool-call',
                'title' => $title,
                'data' => [
                    'calls' => [
                        ['name' => 'assistant.reply', 'status' => 'completed', 'result' => $summary],
                    ],
                ],
            ],
            'settings-form' => [
                'id' => $this->widgetId('settings'),
                'kind' => 'settings-form',
                'title' => $title,
                'data' => [
                    'fields' => [
                        ['name' => 'preference', 'label' => 'Preference', 'type' => 'text', 'value' => $summary],
                    ],
                ],
            ],
            'mcp-server-list' => [
                'id' => $this->widgetId('mcp'),
                'kind' => 'mcp-server-list',
                'title' => $title,
                'data' => [
                    'servers' => [
                        ['name' => 'Business OS', 'url' => 'internal://business-os', 'status' => 'connected'],
                    ],
                ],
            ],
            'project-list' => [
                'id' => $this->widgetId('project'),
                'kind' => 'project-list',
                'title' => $title,
                'data' => [
                    'projects' => [
                        ['id' => 'business-os', 'name' => $title, 'status' => 'active'],
                    ],
                ],
            ],
            default => [
                'id' => $this->widgetId('metric'),
                'kind' => 'metric-card',
                'title' => $title,
                'data' => [
                    'value' => 'Ready',
                    'label' => $summary,
                ],
            ],
        };
    }

    public function detectKind(string $intent): string
    {
        $normalized = Str::lower($intent);

        return match (true) {
            $this->containsAny($normalized, ['server', 'mcp']) => 'mcp-server-list',
            $this->containsAny($normalized, ['project', 'roadmap', 'portfolio']) => 'project-list',
            $this->containsAny($normalized, ['setting', 'settings', 'configure', 'preference']) => 'settings-form',
            $this->containsAny($normalized, ['tool', 'command', 'action']) => 'tool-call',
            $this->containsAny($normalized, ['chat', 'conversation', 'thread']) => 'chat-thread',
            $this->containsAny($normalized, ['log', 'activity', 'audit', 'event']) => 'log-list',
            $this->containsAny($normalized, ['compare', 'comparison', 'breakdown', 'bar']) => 'bar-chart',
            $this->containsAny($normalized, ['trend', 'week', 'month', 'daily', 'history', 'line']) => 'line-chart',
            $this->containsAny($normalized, ['table', 'list', 'jobs', 'invoices', 'quotes', 'customers', 'projects']) => 'data-table',
            default => 'metric-card',
        };
    }

    /**
     * @param  mixed  $parts
     * @return list<array<string, mixed>>
     */
    public function normalize(mixed $parts, string $intent = ''): array
    {
        if (! is_array($parts)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($part) use ($intent) {
            if (! is_array($part)) {
                return null;
            }

            $kind = (string) ($part['kind'] ?? '');

            return [
                'id' => (string) ($part['id'] ?? $this->widgetId($kind !== '' ? $kind : 'widget')),
                'kind' => in_array($kind, self::VALID_KINDS, true) ? $kind : $this->detectKind($intent),
                'title' => $part['title'] ?? $this->titleFromIntent($intent),
                'description' => $part['description'] ?? null,
                'data' => $part['data'] ?? null,
                'props' => $part['props'] ?? null,
            ];
        }, $parts)));
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function titleFromIntent(string $intent): string
    {
        $trimmed = trim($intent);

        if ($trimmed === '') {
            return 'Titan Zero';
        }

        return Str::limit(Str::headline($trimmed), 60);
    }

    private function widgetId(string $prefix): string
    {
        return sprintf('%s-%s', Str::slug($prefix), Str::lower((string) Str::uuid()));
    }
}
