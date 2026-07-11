<?php

namespace Modules\TitanZero\Services\Assist;

class ExplainPageAction
{
    public function run(array $context, string $input = ''): array
    {
        $title = $context['title'] ?? 'This page';
        $headings = $context['headings'] ?? [];
        return [
            'ok' => true,
            'action' => 'explain_page',
            'cards' => [
                [
                    'type' => 'text',
                    'title' => 'What you can do here',
                    'body' => 'Titan Zero (Pass 3) can explain screens, help fill forms, and check missing info. AI execution is connected in later passes.'
                ],
                [
                    'type' => 'list',
                    'title' => 'Detected page info',
                    'items' => array_values(array_filter([
                        'Title: '.$title,
                        !empty($headings) ? 'Headings: '.implode(' • ', array_slice($headings, 0, 6)) : null,
                        isset($context['url']) ? 'URL: '.$context['url'] : null,
                    ])),
                ],
            ],
        ];
    }
}
