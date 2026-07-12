<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocalizedNavigation;
use App\Filament\Resources\RoleResource\Pages;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use UnitEnum;

class RoleResource extends Resource
{
    use HasLocalizedNavigation;

    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = Role::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Roles';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $pluralModelLabel = 'Roles';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 20;

    public static function canDelete(Model $record): bool
    {
        return $record instanceof Role
            && $record->name !== 'Super Admin'
            && parent::canDelete($record);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Role')
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

                Section::make('Permissions')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->label('Permissions')
                            ->relationship('permissions', 'name')
                            ->options(fn (): array => Permission::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                            ->searchable()
                            ->bulkToggleable()
                            ->columnSpanFull(),
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
                    ->color(fn (string $state): string => $state === 'Super Admin' ? 'danger' : 'primary'),

                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->counts('permissions')
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
            ->emptyStateHeading(__('admin.empty_states.roles_heading'))
            ->emptyStateDescription(__('admin.empty_states.roles_description'))
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (Role $record): bool => $record->name !== 'Super Admin'),
            ])
            ->bulkActions([])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
