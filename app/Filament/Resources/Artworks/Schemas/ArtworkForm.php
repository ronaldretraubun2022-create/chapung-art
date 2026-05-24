<?php

namespace App\Filament\Resources\Artworks\Schemas;

use Filament\Forms\Components\FileUpload;
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
                Section::make('Informasi Karya')
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
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

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

                Section::make('Detail Karya')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('artist_name')
                                    ->label('Nama Seniman')
                                    ->nullable()
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                TextInput::make('price')
                                    ->label('Harga')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Select::make('status')
                                    ->label('Status')
                                    ->required()
                                    ->options([
                                        'available' => 'Tersedia',
                                        'sold' => 'Terjual',
                                        'reserved' => 'Reservasi',
                                    ])
                                    ->default('available')
                                    ->native(false)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                TextInput::make('medium')
                                    ->label('Media')
                                    ->nullable()
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                TextInput::make('size')
                                    ->label('Ukuran')
                                    ->nullable()
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                TextInput::make('year')
                                    ->label('Tahun')
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue((int) date('Y') + 1)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                FileUpload::make('thumbnail')
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
                                    ->imagePreviewHeight('250')
                                    ->nullable()
                                    ->columnSpan(2),

                                Toggle::make('is_featured')
                                    ->label('Karya Unggulan')
                                    ->helperText('Centang untuk menandai karya sebagai unggulan.')
                                    ->default(false)
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columns(2),
            ]);
    }
}
