<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocalizedNavigation;
use App\Filament\Resources\AdminNotificationResource\Pages;
use App\Models\AdminNotification;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class AdminNotificationResource extends Resource
{
    use HasLocalizedNavigation;

    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = AdminNotification::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?string $modelLabel = 'Notification';

    protected static ?string $pluralModelLabel = 'Notifications';

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'CMS';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Notification')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                Forms\Components\Textarea::make('message')
                                    ->label('Message')
                                    ->rows(4)
                                    ->nullable()
                                    ->columnSpan(2),

                                Forms\Components\Select::make('type')
                                    ->label('Type')
                                    ->options(self::typeOptions())
                                    ->default('info')
                                    ->required(),

                                Forms\Components\TextInput::make('url')
                                    ->label('URL')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\DateTimePicker::make('read_at')
                                    ->label('Read At')
                                    ->seconds(false)
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(42),

                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->limit(55)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::typeOptions()[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'order' => 'warning',
                        'payment' => 'success',
                        'customer' => 'info',
                        'post' => 'gray',
                        default => 'primary',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('read_at')
                    ->label('Status')
                    ->badge()
                    ->state(fn (AdminNotification $record): string => $record->read_at ? 'Read' : 'Unread')
                    ->color(fn (string $state): string => $state === 'Read' ? 'gray' : 'danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(35)
                    ->copyable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('unread')
                    ->label('Unread only')
                    ->query(fn ($query) => $query->whereNull('read_at')),

                Tables\Filters\SelectFilter::make('type')
                    ->options(self::typeOptions()),
            ])
            ->emptyStateHeading(__('admin.empty_states.notifications_heading'))
            ->emptyStateDescription(__('admin.empty_states.notifications_description'))
            ->actions([
                Action::make('mark_as_read')
                    ->label('Mark as read')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (AdminNotification $record): bool => blank($record->read_at))
                    ->action(fn (AdminNotification $record): mixed => $record->markAsRead()),

                Action::make('open_url')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (AdminNotification $record): ?string => $record->url)
                    ->openUrlInNewTab()
                    ->visible(fn (AdminNotification $record): bool => filled($record->url)),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminNotifications::route('/'),
            'create' => Pages\CreateAdminNotification::route('/create'),
            'edit' => Pages\EditAdminNotification::route('/{record}/edit'),
        ];
    }

    private static function typeOptions(): array
    {
        return [
            'info' => 'Info',
            'order' => 'Order',
            'payment' => 'Payment',
            'customer' => 'Customer',
            'post' => 'Post',
        ];
    }
}
