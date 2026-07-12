<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocalizedNavigation;
use App\Filament\Resources\CollectionResource\Pages;
use App\Models\Collection;
use App\Services\ImageUploadService;
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

class CollectionResource extends Resource
{
    use HasLocalizedNavigation;

    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = Collection::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Collections';

    protected static ?string $modelLabel = 'Collection';

    protected static ?string $pluralModelLabel = 'Collections';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Collection')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Collection')
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

                                Forms\Components\Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(5)
                                    ->nullable()
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Media')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ImageUploadService::configureFilamentUpload(
                                    Forms\Components\FileUpload::make('cover_image')
                                        ->label('Cover Image'),
                                    'collections/covers'
                                )
                                    ->imageEditor()
                                    ->imagePreviewHeight(220)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                ImageUploadService::configureFilamentUpload(
                                    Forms\Components\FileUpload::make('banner_image')
                                        ->label('Banner Image'),
                                    'collections/banners'
                                )
                                    ->imageEditor()
                                    ->imagePreviewHeight(220)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Status')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Featured')
                                    ->helperText('Tampilkan collection sebagai unggulan.')
                                    ->default(false),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->helperText('Nonaktifkan jika collection belum siap tampil.')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->disk('public')
                    ->rounded()
                    ->square()
                    ->height(56)
                    ->width(56),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('artworks_count')
                    ->label('Artworks')
                    ->counts('artworks')
                    ->sortable(),

                Tables\Columns\TextColumn::make('photographies_count')
                    ->label('Photos')
                    ->counts('photographies')
                    ->sortable(),

                Tables\Columns\TextColumn::make('is_active')
                    ->label('Active')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('is_featured')
                    ->label('Featured')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Featured' : 'Regular')
                    ->color(fn (bool $state): string => $state ? 'warning' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
            ])
            ->emptyStateHeading(__('admin.empty_states.collections_heading'))
            ->emptyStateDescription(__('admin.empty_states.collections_description'))
            ->actions([
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
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
        ];
    }
}
