<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtworkResource\Pages;
use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Tag;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;
use BackedEnum;

class ArtworkResource extends Resource
{
    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = Artwork::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Artworks';
    protected static ?string $modelLabel = 'Artwork';
    protected static ?string $pluralModelLabel = 'Artworks';
    protected static ?string $recordTitleAttribute = 'title';
    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Informasi Karya')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Judul Karya')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (?string $state, callable $set): void {
                                        if (filled($state)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Select::make('category_id')
                                    ->label('Kategori')
                                    ->options(fn (): array => Category::query()
                                        ->where('type', 'artwork')
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Textarea::make('excerpt')
                                    ->label('Ringkasan')
                                    ->rows(4)
                                    ->nullable()
                                    ->columnSpan(2),

                                Forms\Components\RichEditor::make('description')
                                    ->label('Deskripsi')
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

                \Filament\Schemas\Components\Section::make('Detail Karya')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('artist_name')
                                    ->label('Nama Seniman')
                                    ->nullable()
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

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
                                    ->helperText('Opsional. Jika kosong, Nama Seniman tetap digunakan sebagai fallback.')
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Select::make('collection_id')
                                    ->label('Collection')
                                    ->options(fn (): array => Collection::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Select::make('tags')
                                    ->label('Tags')
                                    ->relationship(
                                        name: 'tags',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query) => $query
                                            ->where('is_active', true)
                                            ->where(function ($query): void {
                                                $query->whereNull('type')
                                                    ->orWhereIn('type', ['general', 'artwork']);
                                            })
                                            ->orderBy('name')
                                    )
                                    ->getOptionLabelFromRecordUsing(fn (Tag $record): string => $record->name)
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('price')
                                    ->label('Harga')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->required()
                                    ->options([
                                        'available' => 'Tersedia',
                                        'sold' => 'Terjual',
                                        'reserved' => 'Reservasi',
                                    ])
                                    ->default('available')
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('medium')
                                    ->label('Media')
                                    ->nullable()
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('size')
                                    ->label('Ukuran')
                                    ->nullable()
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('year')
                                    ->label('Tahun')
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue((int) date('Y') + 1)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\FileUpload::make('thumbnail')
                                    ->label('Foto Karya')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(4096)
                                    ->disk('public')
                                    ->directory('artworks')
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
                                    ->columnSpan(2),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Karya Unggulan')
                                    ->helperText('Centang untuk menandai karya sebagai unggulan.')
                                    ->default(false)
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('Gallery')
                    ->schema([
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
                                    ->directory('artworks/gallery')
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Foto')
                    ->rounded()
                    ->square()
                    ->height(60)
                    ->width(60),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('collection.name')
                    ->label('Collection')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->limit(28),

                Tables\Columns\TextColumn::make('artist_display_name')
                    ->label('Seniman')
                    ->state(fn (Artwork $record): string => $record->artist_display_name ?: '-')
                    ->searchable(query: fn ($query, string $search) => $query
                        ->where('artist_name', 'like', "%{$search}%")
                        ->orWhereHas('artist', fn ($artistQuery) => $artistQuery->where('name', 'like', "%{$search}%")))
                    ->limit(30)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => $state !== null ? 'Rp ' . number_format((float) $state, 0, ',', '.') : '-'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Tersedia',
                        'sold' => 'Terjual',
                        'reserved' => 'Reservasi',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'sold' => 'danger',
                        'reserved' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Tersedia',
                        'sold' => 'Terjual',
                        'reserved' => 'Reservasi',
                    ]),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Karya Unggulan'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArtworks::route('/'),
            'create' => Pages\CreateArtwork::route('/create'),
            'edit' => Pages\EditArtwork::route('/{record}/edit'),
        ];
    }
}
