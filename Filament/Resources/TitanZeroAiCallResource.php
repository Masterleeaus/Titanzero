<?php

namespace Modules\TitanZero\Filament\Resources;

use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\TitanZero\Entities\TitanZeroAiCall;
use Modules\TitanZero\Filament\Resources\TitanZeroAiCallResource\Pages\ListTitanZeroAiCalls;
use Modules\TitanZero\Filament\Resources\TitanZeroAiCallResource\Pages\ViewTitanZeroAiCall;

/**
 * TitanZeroAiCallResource — audit log viewer for all AI calls.
 *
 * Shows every call routed through TitanZero::query() with full context:
 * prompt, response, model, latency, Aegis verdict, and flags.
 */
class TitanZeroAiCallResource extends Resource
{
    protected static ?string $model = TitanZeroAiCall::class;

    protected static ?string $slug = 'titan-zero/ai-calls';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static \UnitEnum|string|null $navigationGroup = 'Titan Zero';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'AI Audit Log';

    protected static ?string $modelLabel = 'AI Call';

    protected static ?string $pluralModelLabel = 'AI Call Log';

    protected static ?string $recordTitleAttribute = 'id';

    // Audit log: view-only
    public static function canCreate(): bool { return false; }
    public static function canEdit(Model $record): bool { return false; }
    public static function canDelete(Model $record): bool { return false; }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('company_id')
                    ->label('Company')
                    ->sortable(),

                Tables\Columns\TextColumn::make('provider')
                    ->label('Provider')
                    ->badge(),

                Tables\Columns\TextColumn::make('model')
                    ->label('Model')
                    ->searchable(),

                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->searchable(),

                Tables\Columns\TextColumn::make('aegis_verdict')
                    ->label('Aegis')
                    ->badge(),

                Tables\Columns\IconColumn::make('success')
                    ->label('OK')
                    ->boolean(),

                Tables\Columns\TextColumn::make('latency_ms')
                    ->label('Latency (ms)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('provider')
                    ->options([
                        'openai'    => 'OpenAI',
                        'anthropic' => 'Anthropic',
                        'gemini'    => 'Gemini',
                    ]),

                SelectFilter::make('aegis_verdict')
                    ->options([
                        'green'   => 'Green (safe)',
                        'yellow'  => 'Yellow (flagged)',
                        'blocked' => 'Blocked',
                    ]),

                Tables\Filters\Filter::make('failed')
                    ->label('Failed calls only')
                    ->query(fn ($query) => $query->where('success', false)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id')->label('Call ID'),
            TextEntry::make('company_id')->label('Company'),
            TextEntry::make('user_id')->label('User'),
            TextEntry::make('source')->label('Source'),
            TextEntry::make('provider')->label('Provider'),
            TextEntry::make('model')->label('Model'),
            TextEntry::make('context_summary')->label('Context Summary'),
            TextEntry::make('prompt')->label('Prompt'),
            TextEntry::make('response')->label('Response'),
            TextEntry::make('aegis_verdict')->label('Aegis Verdict'),
            TextEntry::make('aegis_flags')
                ->label('Aegis Flags')
                ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state),
            TextEntry::make('latency_ms')->label('Latency (ms)'),
            TextEntry::make('input_tokens')->label('Input Tokens'),
            TextEntry::make('output_tokens')->label('Output Tokens'),
            TextEntry::make('success')
                ->label('Success')
                ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
            TextEntry::make('error_message')->label('Error'),
            TextEntry::make('created_at')->label('Called At')->dateTime(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTitanZeroAiCalls::route('/'),
            'view'  => ViewTitanZeroAiCall::route('/{record}'),
        ];
    }
}
