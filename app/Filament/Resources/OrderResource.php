<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\ShipmentsRelationManager;
use App\Models\Customer;
use App\Models\Order;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class OrderResource extends Resource
{
    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = Order::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Orders';
    protected static ?string $modelLabel = 'Order';
    protected static ?string $pluralModelLabel = 'Orders';
    protected static ?string $recordTitleAttribute = 'order_number';
    protected static string|UnitEnum|null $navigationGroup = 'Commerce';
    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Order Info')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('order_number')
                                    ->label('Order Number')
                                    ->helperText('Otomatis dibuat saat order disimpan.')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('Auto generated')
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Select::make('customer_id')
                                    ->label('Customer')
                                    ->options(fn (): array => Customer::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->nullable()
                                    ->afterStateUpdated(function (?int $state, callable $set): void {
                                        $customer = $state ? Customer::find($state) : null;

                                        if (! $customer) {
                                            return;
                                        }

                                        $set('customer_name', $customer->name);
                                        $set('customer_email', $customer->email);
                                        $set('customer_phone', $customer->phone ?: $customer->whatsapp);
                                    })
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('customer_name')
                                    ->label('Customer Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('customer_email')
                                    ->label('Customer Email')
                                    ->email()
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('customer_phone')
                                    ->label('Customer Phone')
                                    ->tel()
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),
                            ]),
                    ]),

                Section::make('Order Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('Items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Select::make('product_type')
                                    ->label('Product Type')
                                    ->options([
                                        'artwork' => 'Artwork',
                                        'photography' => 'Photography',
                                        'custom' => 'Custom',
                                    ])
                                    ->required()
                                    ->default('artwork'),

                                Forms\Components\TextInput::make('product_id')
                                    ->label('Product ID')
                                    ->numeric()
                                    ->nullable(),

                                Forms\Components\TextInput::make('title')
                                    ->label('Title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('price')
                                    ->label('Price')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->default(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $get, callable $set): mixed => self::updateItemTotal($get, $set)),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $get, callable $set): mixed => self::updateItemTotal($get, $set)),

                                Forms\Components\TextInput::make('total')
                                    ->label('Total')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Order item')
                            ->addActionLabel('Tambah Item')
                            ->columnSpanFull(),
                    ]),

                Section::make('Totals')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('discount_total')
                                    ->label('Discount')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->default(0)
                                    ->required(),

                                Forms\Components\TextInput::make('shipping_total')
                                    ->label('Shipping')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->default(0)
                                    ->required(),

                                Forms\Components\TextInput::make('grand_total')
                                    ->label('Grand Total')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ]),

                Section::make('Status')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Order Status')
                                    ->options(self::statusOptions())
                                    ->default('pending')
                                    ->required(),

                                Forms\Components\Select::make('payment_status')
                                    ->label('Payment Status')
                                    ->options(self::paymentStatusOptions())
                                    ->default('unpaid')
                                    ->required(),

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
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((float) $state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'confirmed', 'processing' => 'info',
                        'shipped' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::paymentStatusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(self::statusOptions()),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(self::paymentStatusOptions()),
            ])
            ->emptyStateHeading('Belum ada order')
            ->emptyStateDescription('Buat order marketplace untuk pembelian artwork atau photography.')
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
            ShipmentsRelationManager::class,
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function paymentStatusOptions(): array
    {
        return [
            'unpaid' => 'Unpaid',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ];
    }

    private static function updateItemTotal(callable $get, callable $set): void
    {
        $price = (float) ($get('price') ?? 0);
        $quantity = max(1, (int) ($get('quantity') ?? 1));

        $set('total', $price * $quantity);
    }
}
