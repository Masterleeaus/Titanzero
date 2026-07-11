<?php

namespace Modules\TitanZero\Services\Assist;

class GenerateNotesAction
{
    public function run(array $context, string $input = ''): array
    {
        $title = $context['title'] ?? 'Page';
        $headings = $context['headings'] ?? [];
        $note = "Notes for {$title}:\n";
        if (!empty($headings)) {
            $note .= "- Key sections: ".implode(', ', array_slice($headings, 0, 6))."\n";
        }
        $note .= "- Next action: confirm required fields and save.\n";

        return [
            'ok' => true,
            'action' => 'generate_notes',
            'cards' => [
                [
                    'type' => 'text',
                    'title' => 'Draft notes',
                    'body' => $note,
                ],
            ],
        ];
    }
}
