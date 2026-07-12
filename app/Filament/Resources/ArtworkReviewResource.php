<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocalizedNavigation;
use App\Filament\Resources\ArtworkReviewResource\Pages;
use App\Models\ArtworkReview;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class ArtworkReviewResource extends Resource
{
    use HasLocalizedNavigation;

    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = ArtworkReview::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Artwork Reviews';

    protected static ?string $modelLabel = 'Artwork Review';

    protected static ?string $pluralModelLabel = 'Artwork Reviews';

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 24;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Review')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options(self::statusOptions())
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, callable $set): void {
                                        if (in_array($state, [ArtworkReview::STATUS_APPROVED, ArtworkReview::STATUS_REJECTED], true)) {
                                            $set('moderated_by', auth()->id());
                                            $set('moderated_at', now());
                                        }
                                    })
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('rating')
                                    ->label('Rating')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(5)
                                    ->required()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('title')
                                    ->label('Title')
                                    ->maxLength(120)
                                    ->nullable()
                                    ->columnSpan(2),

                                Forms\Components\Textarea::make('body')
                                    ->label('Review Body')
                                    ->rows(6)
                                    ->required()
                                    ->maxLength(2000)
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('reviewer_name')
                                    ->label('Reviewer')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('reviewer_email')
                                    ->label('Reviewer Email')
                                    ->email()
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Toggle::make('is_verified_purchase')
                                    ->label('Verified Purchase')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\DateTimePicker::make('moderated_at')
                                    ->label('Moderated At')
                                    ->seconds(false)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Select::make('moderated_by')
                                    ->label('Moderator')
                                    ->relationship('moderator', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Textarea::make('moderation_note')
                                    ->label('Moderation Note')
                                    ->rows(3)
                                    ->maxLength(255)
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
                Tables\Columns\TextColumn::make('artwork.title')
                    ->label('Artwork')
                    ->searchable()
                    ->sortable()
                    ->limit(34),

                Tables\Columns\TextColumn::make('reviewer_name')
                    ->label('Reviewer')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => $state.' / 5')
                    ->color(fn (int $state): string => $state >= 4 ? 'success' : ($state === 3 ? 'warning' : 'danger'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        ArtworkReview::STATUS_APPROVED => 'success',
                        ArtworkReview::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_verified_purchase')
                    ->label('Verified')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(self::statusOptions()),

                Tables\Filters\SelectFilter::make('rating')
                    ->options([5 => '5', 4 => '4', 3 => '3', 2 => '2', 1 => '1']),
            ])
            ->emptyStateHeading(__('admin.empty_states.artwork_reviews_heading'))
            ->emptyStateDescription(__('admin.empty_states.artwork_reviews_description'))
            ->actions([
                Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ArtworkReview $record): bool => $record->status !== ArtworkReview::STATUS_APPROVED)
                    ->action(fn (ArtworkReview $record): mixed => $record->approve(auth()->user())),

                Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ArtworkReview $record): bool => $record->status !== ArtworkReview::STATUS_REJECTED)
                    ->requiresConfirmation()
                    ->action(fn (ArtworkReview $record): mixed => $record->reject(auth()->user())),

                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkAction::make('approveSelected')
                    ->label('Approve selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($records): mixed => $records->each->approve(auth()->user())),

                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArtworkReviews::route('/'),
            'edit' => Pages\EditArtworkReview::route('/{record}/edit'),
        ];
    }

    public static function statusOptions(): array
    {
        return [
            ArtworkReview::STATUS_PENDING => 'Pending',
            ArtworkReview::STATUS_APPROVED => 'Approved',
            ArtworkReview::STATUS_REJECTED => 'Rejected',
        ];
    }
}
