<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotographyResource\Pages;
use App\Models\Artist;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Photography;
use App\Models\Tag;
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

class PhotographyResource extends Resource
{
    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = Photography::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-camera';
    protected static ?string $navigationLabel = 'Photographies';
    protected static ?string $recordTitleAttribute = 'title';
    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Basic Info')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Title')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (?string $state, callable $set): void {
                                        $set('slug', filled($state) ? Str::slug($state) : null);
                                    })
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('excerpt')
                                    ->label('Excerpt')
                                    ->rows(4)
                                    ->nullable()
                                    ->columnSpan(2),

                                Forms\Components\RichEditor::make('description')
                                    ->label('Description')
                                    ->nullable()
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'bulletList',
                                        'orderedList',
                                        'blockquote',
                                        'codeBlock',
                                        'link',
                                        'undo',
                                        'redo',
                                    ])
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Artist & Category')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->label('Category')
                                    ->options(fn (): array => Category::query()
                                        ->where('type', 'photography')
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                Forms\Components\Select::make('artist_id')
                                    ->label('Artist')
                                    ->options(fn (): array => Artist::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->helperText('Opsional. Jika kosong, Photographer tetap digunakan sebagai fallback.'),

                                Forms\Components\TextInput::make('photographer_name')
                                    ->label('Photographer')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\Select::make('collection_id')
                                    ->label('Collection')
                                    ->options(fn (): array => Collection::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                Forms\Components\Select::make('tags')
                                    ->label('Tags')
                                    ->relationship(
                                        name: 'tags',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query) => $query
                                            ->where('is_active', true)
                                            ->where(function ($query): void {
                                                $query->whereNull('type')
                                                    ->orWhereIn('type', ['general', 'photography']);
                                            })
                                            ->orderBy('name')
                                    )
                                    ->getOptionLabelFromRecordUsing(fn (Tag $record): string => $record->name)
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Camera Metadata')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('camera')
                                    ->label('Camera')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\TextInput::make('lens')
                                    ->label('Lens')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\TextInput::make('iso')
                                    ->label('ISO')
                                    ->numeric()
                                    ->minValue(0)
                                    ->nullable(),

                                Forms\Components\TextInput::make('aperture')
                                    ->label('Aperture')
                                    ->maxLength(50)
                                    ->nullable(),

                                Forms\Components\TextInput::make('shutter_speed')
                                    ->label('Shutter Speed')
                                    ->maxLength(50)
                                    ->nullable(),

                                Forms\Components\TextInput::make('focal_length')
                                    ->label('Focal Length')
                                    ->maxLength(50)
                                    ->nullable(),

                                Forms\Components\DateTimePicker::make('taken_at')
                                    ->label('Taken At')
                                    ->native(false)
                                    ->nullable(),
                            ]),
                    ]),

                Section::make('Location')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('location')
                                    ->label('Location')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\TextInput::make('province')
                                    ->label('Province')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\TextInput::make('country')
                                    ->label('Country')
                                    ->default('Indonesia')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\TextInput::make('gps_lat')
                                    ->label('GPS Latitude')
                                    ->numeric()
                                    ->nullable(),

                                Forms\Components\TextInput::make('gps_lng')
                                    ->label('GPS Longitude')
                                    ->numeric()
                                    ->nullable(),
                            ]),
                    ]),

                Section::make('License & Price')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('license')
                                    ->label('License')
                                    ->options([
                                        'all_rights_reserved' => 'All Rights Reserved',
                                        'creative_commons' => 'Creative Commons',
                                        'editorial' => 'Editorial Use',
                                        'commercial' => 'Commercial Use',
                                    ])
                                    ->nullable(),

                                Forms\Components\TextInput::make('price')
                                    ->label('Price')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->nullable(),

                                Forms\Components\TextInput::make('stock')
                                    ->label('Stock')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(1)
                                    ->required(),

                                Forms\Components\TextInput::make('views')
                                    ->label('Views')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(),
                            ]),
                    ]),

                Section::make('SEO')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('seo_title')
                                    ->label('SEO Title')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\Textarea::make('seo_description')
                                    ->label('SEO Description')
                                    ->rows(4)
                                    ->nullable(),

                                Forms\Components\FileUpload::make('og_image')
                                    ->label('OG Image')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(4096)
                                    ->disk('public')
                                    ->directory('photographies/og')
                                    ->getUploadedFileNameForStorageUsing(fn ($file): string => Str::uuid().'.'.match ($file->getMimeType()) {
                                        'image/jpeg' => 'jpg',
                                        'image/png' => 'png',
                                        'image/webp' => 'webp',
                                        default => 'bin',
                                    })
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->imagePreviewHeight(180)
                                    ->nullable()
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('thumbnail')
                            ->label('Thumbnail')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(4096)
                            ->disk('public')
                            ->directory('photographies')
                            ->getUploadedFileNameForStorageUsing(fn ($file): string => Str::uuid().'.'.match ($file->getMimeType()) {
                                'image/jpeg' => 'jpg',
                                'image/png' => 'png',
                                'image/webp' => 'webp',
                                default => 'bin',
                            })
                            ->visibility('public')
                            ->imageEditor()
                            ->imagePreviewHeight(250)
                            ->nullable()
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('mediaItems')
                            ->label('Gallery Images')
                            ->relationship('mediaItems')
                            ->schema([
                                Forms\Components\FileUpload::make('file_path')
                                    ->label('Image')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(4096)
                                    ->disk('public')
                                    ->directory('photographies/gallery')
                                    ->getUploadedFileNameForStorageUsing(fn ($file): string => Str::uuid().'.'.match ($file->getMimeType()) {
                                        'image/jpeg' => 'jpg',
                                        'image/png' => 'png',
                                        'image/webp' => 'webp',
                                        default => 'bin',
                                    })
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->imagePreviewHeight(180)
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\Hidden::make('collection_name')
                                    ->default('gallery'),

                                Forms\Components\Hidden::make('file_type')
                                    ->default('image'),

                                Forms\Components\TextInput::make('title')
                                    ->label('Title')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\TextInput::make('alt_text')
                                    ->label('Alt Text')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Order')
                                    ->numeric()
                                    ->default(0),

                                Forms\Components\Toggle::make('is_cover')
                                    ->label('Gallery Cover')
                                    ->default(false),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Gallery image')
                            ->addActionLabel('Tambah Gambar')
                            ->columnSpanFull(),
                    ]),

                Section::make('Status')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->required()
                                    ->options([
                                        'available' => 'Available',
                                        'sold' => 'Sold',
                                        'reserved' => 'Reserved',
                                    ])
                                    ->default('available'),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Featured')
                                    ->helperText('Centang untuk menandai foto sebagai unggulan.')
                                    ->default(false),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Image')
                    ->rounded()
                    ->square()
                    ->height(56)
                    ->width(56),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(36),

                Tables\Columns\TextColumn::make('artist_display_name')
                    ->label('Artist')
                    ->state(fn (Photography $record): string => $record->artist_display_name ?: '-')
                    ->searchable(query: fn ($query, string $search) => $query
                        ->where('photographer_name', 'like', "%{$search}%")
                        ->orWhereHas('artist', fn ($artistQuery) => $artistQuery->where('name', 'like', "%{$search}%")))
                    ->limit(28)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('camera')
                    ->label('Camera')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->limit(24),

                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->limit(24),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => $state !== null ? 'Rp ' . number_format((float) $state, 0, ',', '.') : '-'),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Available',
                        'sold' => 'Sold',
                        'reserved' => 'Reserved',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'sold' => 'danger',
                        'reserved' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('is_featured')
                    ->label('Featured')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Featured' : 'No')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'sold' => 'Sold',
                        'reserved' => 'Reserved',
                    ]),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
            ])
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
            'index' => Pages\ListPhotographies::route('/'),
            'create' => Pages\CreatePhotography::route('/create'),
            'edit' => Pages\EditPhotography::route('/{record}/edit'),
        ];
    }
}
