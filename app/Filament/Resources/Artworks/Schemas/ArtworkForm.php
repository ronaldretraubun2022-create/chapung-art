<?php

namespace App\Filament\Resources\Artworks\Schemas;

use App\Models\Artist;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Tag;
use App\Services\DigitalDownloadService;
use App\Services\ImageUploadService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArtworkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Info')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul Karya')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (?string $state, callable $set): void {
                                        $set('slug', filled($state) ? Str::slug($state) : null);
                                    })
                                    ->maxLength(255),

                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->nullable(),

                                TextInput::make('year')
                                    ->label('Tahun')
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue((int) date('Y') + 1)
                                    ->nullable(),

                                Textarea::make('excerpt')
                                    ->label('Ringkasan')
                                    ->rows(4)
                                    ->nullable()
                                    ->columnSpan(2),

                                RichEditor::make('description')
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

                Section::make('Artist & Category')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('category_id')
                                    ->label('Kategori')
                                    ->options(fn (): array => Category::query()
                                        ->where('type', 'artwork')
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                Select::make('artist_id')
                                    ->label('Artist')
                                    ->options(fn (): array => Artist::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->helperText('Opsional. Jika kosong, Nama Seniman tetap digunakan sebagai fallback.'),

                                TextInput::make('artist_name')
                                    ->label('Nama Seniman')
                                    ->maxLength(255)
                                    ->nullable(),

                                Select::make('collection_id')
                                    ->label('Collection')
                                    ->options(fn (): array => Collection::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                Select::make('tags')
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
                            ]),
                    ])
                    ->columns(2),

                Section::make('Price & Inventory')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('price')
                                    ->label('Harga')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->nullable(),

                                TextInput::make('stock')
                                    ->label('Stock')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(1)
                                    ->required(),

                                TextInput::make('views')
                                    ->label('Views')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(),

                                TextInput::make('likes')
                                    ->label('Likes')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Dimension')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('medium')
                                    ->label('Media')
                                    ->maxLength(255)
                                    ->nullable(),

                                TextInput::make('material')
                                    ->label('Material')
                                    ->maxLength(255)
                                    ->nullable(),

                                TextInput::make('technique')
                                    ->label('Technique')
                                    ->maxLength(255)
                                    ->nullable(),

                                Select::make('orientation')
                                    ->label('Orientation')
                                    ->options([
                                        'portrait' => 'Portrait',
                                        'landscape' => 'Landscape',
                                        'square' => 'Square',
                                    ])
                                    ->nullable(),

                                TextInput::make('frame')
                                    ->label('Frame')
                                    ->maxLength(255)
                                    ->nullable(),

                                TextInput::make('size')
                                    ->label('Ukuran')
                                    ->maxLength(255)
                                    ->nullable(),

                                TextInput::make('width')
                                    ->label('Width (cm)')
                                    ->numeric()
                                    ->nullable(),

                                TextInput::make('height')
                                    ->label('Height (cm)')
                                    ->numeric()
                                    ->nullable(),

                                TextInput::make('depth')
                                    ->label('Depth (cm)')
                                    ->numeric()
                                    ->nullable(),

                                TextInput::make('weight')
                                    ->label('Weight (kg)')
                                    ->numeric()
                                    ->nullable(),

                                Select::make('condition')
                                    ->label('Condition')
                                    ->options([
                                        'new' => 'New',
                                        'excellent' => 'Excellent',
                                        'good' => 'Good',
                                        'fair' => 'Fair',
                                    ])
                                    ->nullable(),

                                TextInput::make('location')
                                    ->label('Location')
                                    ->maxLength(255)
                                    ->nullable(),
                            ]),
                    ]),

                Section::make('Certificate')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('certificate_number')
                                    ->label('Certificate Number')
                                    ->maxLength(255)
                                    ->nullable(),

                                Select::make('license')
                                    ->label('License')
                                    ->options([
                                        'all_rights_reserved' => 'All Rights Reserved',
                                        'creative_commons' => 'Creative Commons',
                                        'editorial' => 'Editorial Use',
                                        'commercial' => 'Commercial Use',
                                    ])
                                    ->nullable(),
                            ]),
                    ]),

                Section::make('Digital Download')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('digital_download_enabled')
                                    ->label('Aktifkan Download Digital')
                                    ->helperText('File disimpan di private storage dan hanya dapat diunduh oleh pembeli terotorisasi.')
                                    ->default(false),

                                FileUpload::make('digital_file_path')
                                    ->label('File Digital Private')
                                    ->disk('local')
                                    ->directory('artworks/digital')
                                    ->visibility('private')
                                    ->acceptedFileTypes(DigitalDownloadService::allowedMimeTypes())
                                    ->maxSize(DigitalDownloadService::maxKilobytes())
                                    ->storeFileNamesIn('digital_file_name')
                                    ->getUploadedFileNameForStorageUsing(fn ($file): string => app(DigitalDownloadService::class)->uniqueFileName($file))
                                    ->saveUploadedFileUsing(fn ($component, $file): ?string => app(DigitalDownloadService::class)->storeFilamentUpload($component, $file))
                                    ->deleteUploadedFileUsing(fn (?string $file): mixed => app(DigitalDownloadService::class)->delete($file))
                                    ->helperText('Format aman: PDF, JPEG, PNG, WebP. Jangan unggah ZIP, executable, atau file master yang tidak untuk pembeli.')
                                    ->nullable()
                                    ->columnSpan(2),
                            ]),
                    ]),

                Section::make('SEO')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('seo_title')
                                    ->label('SEO Title')
                                    ->maxLength(255)
                                    ->nullable(),

                                Textarea::make('seo_description')
                                    ->label('SEO Description')
                                    ->rows(4)
                                    ->nullable(),

                                ImageUploadService::configureFilamentUpload(
                                    FileUpload::make('og_image')
                                        ->label('OG Image'),
                                    'artworks/og'
                                )
                                    ->imageEditor()
                                    ->imagePreviewHeight('180')
                                    ->nullable()
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Media')
                    ->schema([
                        ImageUploadService::configureFilamentUpload(
                            FileUpload::make('thumbnail')
                                ->label('Foto Karya'),
                            'artworks'
                        )
                            ->imageEditor()
                            ->imagePreviewHeight('250')
                            ->nullable()
                            ->columnSpanFull(),

                        Repeater::make('mediaItems')
                            ->label('Gallery Images')
                            ->relationship('mediaItems')
                            ->schema([
                                ImageUploadService::configureFilamentUpload(
                                    FileUpload::make('file_path')
                                        ->label('Image'),
                                    'artworks/gallery'
                                )
                                    ->imageEditor()
                                    ->imagePreviewHeight('180')
                                    ->required()
                                    ->columnSpan(2),

                                Hidden::make('collection_name')
                                    ->default('gallery'),

                                Hidden::make('file_type')
                                    ->default('image'),

                                TextInput::make('title')
                                    ->label('Title')
                                    ->maxLength(255)
                                    ->nullable(),

                                TextInput::make('alt_text')
                                    ->label('Alt Text')
                                    ->maxLength(255)
                                    ->nullable(),

                                TextInput::make('sort_order')
                                    ->label('Order')
                                    ->numeric()
                                    ->default(0),

                                Toggle::make('is_cover')
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
                                Select::make('status')
                                    ->label('Status')
                                    ->required()
                                    ->options([
                                        'available' => 'Tersedia',
                                        'sold' => 'Terjual',
                                        'reserved' => 'Reservasi',
                                    ])
                                    ->default('available')
                                    ->native(false),

                                Toggle::make('is_featured')
                                    ->label('Karya Unggulan')
                                    ->helperText('Centang untuk menandai karya sebagai unggulan.')
                                    ->default(false),
                            ]),
                    ]),
            ]);
    }
}
