<?php

namespace Modules\TitanZero\Services\Assist;

class CheckMissingAction
{
    public function run(array $context, string $input = ''): array
    {
        $missing = [];
        foreach (($context['forms'] ?? []) as $form) {
            foreach (($form['fields'] ?? []) as $f) {
                $name = $f['name'] ?? '';
                $value = trim((string)($f['value'] ?? ''));
                if (!$name) continue;
                if ($value === '') {
                    $missing[] = $name;
                }
            }
        }

        return [
            'ok' => true,
            'action' => 'check_missing',
            'cards' => [
                [
                    'type' => 'list',
                    'title' => 'Empty fields detected',
                    'items' => empty($missing) ? ['No empty fields detected in sampled forms.'] : array_slice($missing, 0, 20),
                ],
                [
                    'type' => 'text',
                    'title' => 'Next step',
                    'body' => 'Use “Fill form” to generate placeholders, or Pass 6+ for record-aware suggestions.'
                ],
            ],
        ];
    }
}
