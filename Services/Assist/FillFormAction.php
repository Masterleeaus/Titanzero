<?php

namespace Modules\TitanZero\Services\Assist;

class FillFormAction
{
    public function run(array $context, string $input = ''): array
    {
        $suggestions = [];
        foreach (($context['forms'] ?? []) as $form) {
            foreach (($form['fields'] ?? []) as $f) {
                $name = $f['name'] ?? '';
                if (!$name) continue;
                if (preg_match('/email/i', $name)) {
                    $suggestions[] = ['field' => $name, 'value' => 'example@company.com', 'reason' => 'Example placeholder (replace with real value)'];
                }
                if (preg_match('/phone|mobile/i', $name)) {
                    $suggestions[] = ['field' => $name, 'value' => '+61 4xx xxx xxx', 'reason' => 'Example AU format'];
                }
            }
        }

        if (empty($suggestions)) {
            $suggestions[] = ['field' => '(none)', 'value' => '', 'reason' => 'No obvious fields detected on this page'];
        }

        return [
            'ok' => true,
            'action' => 'fill_form',
            'cards' => [
                [
                    'type' => 'text',
                    'title' => 'Suggested values (safe placeholders)',
                    'body' => 'These are placeholders only. Pass 4+ will use standards + business context to suggest real values.'
                ],
                [
                    'type' => 'suggestions',
                    'title' => 'Field suggestions',
                    'suggestions' => array_slice($suggestions, 0, 12),
                ]
            ],
        ];
    }
}
