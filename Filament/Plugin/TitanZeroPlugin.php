<?php

namespace Modules\TitanZero\Filament\Plugin;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Modules\TitanZero\Filament\Pages\TitanZeroChatPage;
use Modules\TitanZero\Filament\Resources\TitanZeroAiCallResource;
use Modules\TitanZero\Filament\Widgets\TitanZeroEmbedWidget;

/**
 * TitanZeroPlugin — Filament plugin for the Titan Zero AI Orchestration node.
 *
 * Registers:
 * - TitanZeroChatPage: full-screen AI chat interface at path /zero
 * - TitanZeroAiCallResource: audit log viewer for all AI calls
 * - TitanZeroEmbedWidget: collapsible embedded AI panel for use in all node layouts
 */
class TitanZeroPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'titan-zero';
    }

    public function register(Panel $panel): void
    {
        $panel
            // PASS14: disabled panel page registration; Filament v4 rejects raw class-string pages.
            ->resources([
                TitanZeroAiCallResource::class,
            ])
            ->widgets([
                TitanZeroEmbedWidget::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
