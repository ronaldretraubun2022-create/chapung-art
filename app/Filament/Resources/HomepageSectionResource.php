<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomepageSectionResource\Pages;
use App\Models\HomepageSection;
use App\Services\ImageUploadService;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class HomepageSectionResource extends Resource
{
    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = HomepageSection::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationLabel = 'Homepage Sections';
    protected static ?string $modelLabel = 'Homepage Section';
    protected static ?string $pluralModelLabel = 'Homepage Sections';
    protected static ?string $recordTitleAttribute = 'section_key';
    protected static string|UnitEnum|null $navigationGroup = 'CMS';
    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Section Content')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('section_key')
                                    ->label('Section Key')
                                    ->helperText('Contoh: hero, featured_artworks, footer.')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->rule('regex:/^[a-z0-9_\-]+$/')
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('title')
                                    ->label('Title')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('subtitle')
                                    ->label('Subtitle')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(2),

                                Forms\Components\Textarea::make('content')
                                    ->label('Content')
                                    ->rows(5)
                                    ->nullable()
                                    ->columnSpan(2),
                            ]),
                    ]),

                Section::make('Media & Action')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ImageUploadService::configureFilamentUpload(
                                    Forms\Components\FileUpload::make('image')
                                        ->label('Image'),
                                    'homepage-sections'
                                )
                                    ->imageEditor()
                                    ->imagePreviewHeight(220)
                                    ->nullable()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('button_text')
                                    ->label('Button Text')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('button_url')
                                    ->label('Button URL')
                                    ->helperText('Bisa memakai path internal seperti /gallery atau URL lengkap.')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),
                            ]),
                    ]),

                Section::make('Display Settings')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->required()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->helperText('Section nonaktif tidak dibaca di homepage.')
                                    ->default(true)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\KeyValue::make('payload')
                                    ->label('Payload')
                                    ->helperText('Data tambahan opsional untuk kebutuhan konten lanjutan.')
                                    ->keyLabel('Key')
                                    ->valueLabel('Value')
                                    ->nullable()
                                    ->columnSpan(2),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->rounded()
                    ->square()
                    ->height(56)
                    ->width(56),

                Tables\Columns\TextColumn::make('section_key')
                    ->label('Section Key')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(45)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('is_active')
                    ->label('Active')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->emptyStateHeading('Belum ada homepage section')
            ->emptyStateDescription('Tambahkan section untuk mengelola konten homepage dari CMS.')
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomepageSections::route('/'),
            'create' => Pages\CreateHomepageSection::route('/create'),
            'edit' => Pages\EditHomepageSection::route('/{record}/edit'),
        ];
    }
}
