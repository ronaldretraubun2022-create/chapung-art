<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StatusHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistories';

    protected static ?string $title = 'Status History';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('status_from')
                    ->label('Status From')
                    ->placeholder('-')
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? str($state)->headline()->toString() : '-'),

                Tables\Columns\TextColumn::make('status_to')
                    ->label('Status To')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? str($state)->headline()->toString() : '-'),

                Tables\Columns\TextColumn::make('payment_status_from')
                    ->label('Payment From')
                    ->placeholder('-')
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? str($state)->headline()->toString() : '-'),

                Tables\Columns\TextColumn::make('payment_status_to')
                    ->label('Payment To')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? str($state)->headline()->toString() : '-'),

                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->badge(),

                Tables\Columns\TextColumn::make('changedBy.name')
                    ->label('Changed By')
                    ->placeholder('System'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->emptyStateHeading(__('admin.empty_states.status_histories_heading'))
            ->emptyStateDescription(__('admin.empty_states.status_histories_description'))
            ->defaultSort('created_at', 'desc');
    }
}
