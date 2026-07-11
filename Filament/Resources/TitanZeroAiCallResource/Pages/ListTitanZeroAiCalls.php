<?php

namespace Modules\TitanZero\Filament\Resources\TitanZeroAiCallResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\TitanZero\Filament\Resources\TitanZeroAiCallResource;

class ListTitanZeroAiCalls extends ListRecords
{
    protected static string $resource = TitanZeroAiCallResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
