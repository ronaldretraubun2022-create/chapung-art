<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Filament\Resources\ShipmentResource;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ShipmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'shipments';

    protected static ?string $title = 'Shipments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('courier')
                            ->label('Courier')
                            ->maxLength(255)
                            ->nullable(),

                        Forms\Components\TextInput::make('tracking_number')
                            ->label('Tracking Number')
                            ->maxLength(255)
                            ->nullable(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(ShipmentResource::statusOptions())
                            ->default('pending')
                            ->required(),

                        Forms\Components\TextInput::make('shipping_cost')
                            ->label('Shipping Cost')
                            ->numeric()
                            ->prefix('Rp ')
                            ->default(fn (): mixed => $this->getOwnerRecord()->shipping_total)
                            ->required(),

                        Forms\Components\TextInput::make('origin')
                            ->label('Origin')
                            ->maxLength(255)
                            ->nullable(),

                        Forms\Components\TextInput::make('destination')
                            ->label('Destination')
                            ->maxLength(255)
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('shipped_at')
                            ->label('Shipped At')
                            ->seconds(false)
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('delivered_at')
                            ->label('Delivered At')
                            ->seconds(false)
                            ->nullable(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->nullable()
                            ->columnSpan(2),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tracking_number')
            ->columns([
                Tables\Columns\TextColumn::make('courier')
                    ->label('Courier')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('tracking_number')
                    ->label('Tracking Number')
                    ->copyable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('destination')
                    ->label('Destination')
                    ->limit(32)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('shipping_cost')
                    ->label('Cost')
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((float) $state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ShipmentResource::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'packed' => 'info',
                        'shipped' => 'warning',
                        'delivered' => 'success',
                        'returned' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('shipped_at')
                    ->label('Shipped At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
