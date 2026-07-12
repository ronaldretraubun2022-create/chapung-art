<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocalizedNavigation;
use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ActivityLogResource extends Resource
{
    use HasLocalizedNavigation;

    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = ActivityLog::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Activity Logs';

    protected static ?string $modelLabel = 'Activity Log';

    protected static ?string $pluralModelLabel = 'Activity Logs';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::actionOptions()[$state] ?? str($state)->replace('_', ' ')->title())
                    ->color(fn (string $state): string => match ($state) {
                        'login' => 'info',
                        'create' => 'success',
                        'update', 'order_update' => 'warning',
                        'delete' => 'danger',
                        'publish' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->placeholder('System'),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject_id')
                    ->label('ID')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(70)
                    ->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options(self::actionOptions()),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload(),
            ])
            ->emptyStateHeading(__('admin.empty_states.activity_logs_heading'))
            ->emptyStateDescription(__('admin.empty_states.activity_logs_description'))
            ->actions([])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }

    private static function actionOptions(): array
    {
        return [
            'login' => 'Login',
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
            'publish' => 'Publish',
            'order_update' => 'Order Update',
        ];
    }
}
