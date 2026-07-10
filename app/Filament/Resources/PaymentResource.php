<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Order;
use App\Models\Payment;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class PaymentResource extends Resource
{
    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = Payment::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Payments';
    protected static ?string $modelLabel = 'Payment';
    protected static ?string $pluralModelLabel = 'Payments';
    protected static ?string $recordTitleAttribute = 'id';
    protected static string|UnitEnum|null $navigationGroup = 'Commerce';
    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Payment')
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
                                            $set('amount', $order->grand_total);
                                        }
                                    })
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Select::make('payment_method')
                                    ->label('Payment Method')
                                    ->options(self::methodOptions())
                                    ->default('manual_transfer')
                                    ->required()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('amount')
                                    ->label('Amount')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->default(0)
                                    ->required()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options(self::statusOptions())
                                    ->default('pending')
                                    ->required()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\DateTimePicker::make('paid_at')
                                    ->label('Paid At')
                                    ->seconds(false)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\FileUpload::make('proof_image')
                                    ->label('Proof Image')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(4096)
                                    ->disk('public')
                                    ->directory('payments/proofs')
                                    ->getUploadedFileNameForStorageUsing(fn ($file): string => Str::uuid().'.'.match ($file->getMimeType()) {
                                        'image/jpeg' => 'jpg',
                                        'image/png' => 'png',
                                        'image/webp' => 'webp',
                                        default => 'bin',
                                    })
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->imagePreviewHeight(220)
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

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::methodOptions()[$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((float) $state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\ImageColumn::make('proof_image')
                    ->label('Proof')
                    ->disk('public')
                    ->rounded()
                    ->height(48)
                    ->width(48),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options(self::methodOptions()),

                Tables\Filters\SelectFilter::make('status')
                    ->options(self::statusOptions()),
            ])
            ->emptyStateHeading('Belum ada payment')
            ->emptyStateDescription('Tambahkan pembayaran untuk order marketplace.')
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function methodOptions(): array
    {
        return [
            'manual_transfer' => 'Manual Transfer',
            'qris' => 'QRIS',
            'cash' => 'Cash',
            'midtrans' => 'Midtrans',
            'xendit' => 'Xendit',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ];
    }
}
