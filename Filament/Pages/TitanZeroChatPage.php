<?php

namespace Modules\TitanZero\Filament\Pages;

use Filament\Pages\Page;

/**
 * TitanZeroChatPage — full-screen AI chat interface.
 *
 * Panel ID: titan-zero
 * Path: /zero
 *
 * Users can chat with Titan Zero, switch context (job/financials/crew),
 * generate documents, and access suggested prompts per vertical.
 */
class TitanZeroChatPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static \UnitEnum|string|null $navigationGroup = 'Titan Zero';

    protected static ?string $navigationLabel = 'AI Chat';

    protected static ?string $title = 'Titan Zero — AI Chat';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'zero';

    protected string $view = 'filament.pages.suite-placeholder';

    protected function getViewData(): array
    {
        return [
            'emoji'       => '🤖',
            'module'      => 'Titan Zero — AI Chat',
            'description' => 'Full-screen AI chat interface. Ask about any job, client, financial summary, or crew — Titan Zero has vertical-trained context.',
            'capabilities' => [
                'Context-aware chat: ask about a job, client, or site',
                'Document generation mode — produce artefacts from conversation',
                'Suggested prompts per vertical (cleaning, NDIS, and more)',
                'Response streaming support',
                'Session history per chat',
            ],
        ];
    }
}
