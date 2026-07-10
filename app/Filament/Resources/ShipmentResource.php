<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShipmentResource\Pages;
use App\Models\Order;
use App\Models\Shipment;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class ShipmentResource extends Resource
{
    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = Shipment::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Shipments';
    protected static ?string $modelLabel = 'Shipment';
    protected static ?string $pluralModelLabel = 'Shipments';
    protected static ?string $recordTitleAttribute = 'tracking_number';
    protected static string|UnitEnum|null $navigationGroup = 'Commerce';
    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Shipment')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('order_id')
                                    ->label('Order')
                                    ->options(fn (): array => Order::query()
                                        ->latest()
                                        ->limit(100)
                                        ->pluck('order_number', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (?int $state, callable $set): void {
                                        $order = $state ? Order::find($state) : null;

                                        if ($order) {
                                            $set('shipping_cost', $order->shipping_total);
                                        }
                                    })
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('courier')
                                    ->label('Courier')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('tracking_number')
                                    ->label('Tracking Number')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options(self::statusOptions())
                                    ->default('pending')
                                    ->required()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('origin')
                                    ->label('Origin')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('destination')
                                    ->label('Destination')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('shipping_cost')
                                    ->label('Shipping Cost')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->default(0)
                                    ->required()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\DateTimePicker::make('shipped_at')
                                    ->label('Shipped At')
                                    ->seconds(false)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\DateTimePicker::make('delivered_at')
                                    ->label('Delivered At')
                                    ->seconds(false)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Textarea::make('notes')
                                    ->label('Notes')
                                    ->rows(4)
                                    ->nullable()
                                    ->columnSpan(2),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('courier')
                    ->label('Courier')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('tracking_number')
                    ->label('Tracking Number')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('destination')
                    ->label('Destination')
                    ->searchable()
                    ->limit(32)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('shipping_cost')
                    ->label('Cost')
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((float) $state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'packed' => 'info',
                        'shipped' => 'warning',
                        'delivered' => 'success',
                        'returned' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('shipped_at')
                    ->label('Shipped At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(self::statusOptions()),
            ])
            ->emptyStateHeading('Belum ada shipment')
            ->emptyStateDescription('Tambahkan pengiriman untuk order marketplace.')
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShipments::route('/'),
            'create' => Pages\CreateShipment::route('/create'),
            'edit' => Pages\EditShipment::route('/{record}/edit'),
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'packed' => 'Packed',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'returned' => 'Returned',
        ];
    }
}
