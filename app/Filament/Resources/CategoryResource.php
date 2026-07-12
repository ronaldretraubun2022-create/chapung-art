<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocalizedNavigation;
use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
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
use Illuminate\Support\Str;
use UnitEnum;

class CategoryResource extends Resource
{
    use HasLocalizedNavigation;

    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = Category::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Categories';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Kategori')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (?string $state, callable $set): void {
                                        if ($state !== null) {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Textarea::make('description')
                                    ->rows(4)
                                    ->columnSpan(2),

                                Forms\Components\Select::make('type')
                                    ->required()
                                    ->options([
                                        'artwork' => 'Artwork',
                                        'photography' => 'Photography',
                                        'post' => 'Post',
                                        'general' => 'General',
                                    ])
                                    ->default('general')
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->helperText('Nonaktifkan kategori jika tidak lagi digunakan.')
                                    ->columnSpan(['default' => 2, 'md' => 1]),
                            ]),
                    ])
                    ->columns(2),
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
                    ->limit(30),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'artwork' => 'Artwork',
                        'photography' => 'Photography',
                        'post' => 'Post',
                        'general' => 'General',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'artwork' => 'warning',
                        'photography' => 'info',
                        'post' => 'success',
                        'general' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('is_active')
                    ->label('Active')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'artwork' => 'Artwork',
                        'photography' => 'Photography',
                        'post' => 'Post',
                        'general' => 'General',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
