<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocalizedNavigation;
use App\Filament\Resources\PermissionResource\Pages;
use BackedEnum;
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
use Spatie\Permission\Models\Permission;
use UnitEnum;

class PermissionResource extends Resource
{
    use HasLocalizedNavigation;

    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = Permission::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Permissions';

    protected static ?string $modelLabel = 'Permission';

    protected static ?string $pluralModelLabel = 'Permissions';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Permission')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                Forms\Components\TextInput::make('guard_name')
                                    ->label('Guard')
                                    ->default('web')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles_count')
                    ->label('Roles')
                    ->counts('roles')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('guard_name')
                    ->label('Guard')
                    ->options(['web' => 'web']),
            ])
            ->emptyStateHeading(__('admin.empty_states.permissions_heading'))
            ->emptyStateDescription(__('admin.empty_states.permissions_description'))
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }
}
