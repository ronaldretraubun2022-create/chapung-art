<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Filament\Resources\PaymentResource;
use App\Services\ImageUploadService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options(PaymentResource::methodOptions())
                            ->default('manual_transfer')
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('Rp ')
                            ->default(fn (): mixed => $this->getOwnerRecord()->grand_total)
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(PaymentResource::statusOptions())
                            ->default('pending')
                            ->required(),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Paid At')
                            ->seconds(false)
                            ->nullable(),

                        ImageUploadService::configureFilamentUpload(
                            Forms\Components\FileUpload::make('proof_image')
                                ->label('Proof Image'),
                            'payments/proofs',
                            'local',
                            'private'
                        )
                            ->imageEditor()
                            ->imagePreviewHeight(180)
                            ->nullable()
                            ->columnSpan(2),

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
            ->recordTitleAttribute('payment_method')
            ->columns([
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PaymentResource::methodOptions()[$state] ?? $state),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((float) $state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PaymentResource::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\ImageColumn::make('proof_image')
                    ->label('Proof')
                    ->disk('local')
                    ->rounded()
                    ->height(48)
                    ->width(48),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
