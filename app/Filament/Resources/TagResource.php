<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocalizedNavigation;
use App\Filament\Resources\TagResource\Pages;
use App\Models\Tag;
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

class TagResource extends Resource
{
    use HasLocalizedNavigation;

    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = Tag::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-hashtag';

    protected static ?string $navigationLabel = 'Tags';

    protected static ?string $modelLabel = 'Tag';

    protected static ?string $pluralModelLabel = 'Tags';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Tag')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Tag')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (?string $state, callable $set): void {
                                        $set('slug', filled($state) ? Str::slug($state) : null);
                                    })
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Select::make('type')
                                    ->label('Type')
                                    ->options([
                                        'artwork' => 'Artwork',
                                        'photography' => 'Photography',
                                        'post' => 'Post',
                                        'general' => 'General',
                                    ])
                                    ->searchable()
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->helperText('Nonaktifkan tag jika belum siap digunakan.')
                                    ->default(true)
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
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->placeholder('General')
                    ->formatStateUsing(fn (?string $state): string => $state ? Str::headline($state) : 'General')
                    ->color(fn (?string $state): string => match ($state) {
                        'artwork' => 'warning',
                        'photography' => 'info',
                        'post' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('artworks_count')
                    ->label('Artworks')
                    ->counts('artworks')
                    ->sortable(),

                Tables\Columns\TextColumn::make('photographies_count')
                    ->label('Photos')
                    ->counts('photographies')
                    ->sortable(),

                Tables\Columns\TextColumn::make('posts_count')
                    ->label('Posts')
                    ->counts('posts')
                    ->sortable(),

                Tables\Columns\TextColumn::make('is_active')
                    ->label('Active')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
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
            ->emptyStateHeading(__('admin.empty_states.tags_heading'))
            ->emptyStateDescription(__('admin.empty_states.tags_description'))
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
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }
}
