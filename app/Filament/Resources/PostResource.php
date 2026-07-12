<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocalizedNavigation;
use App\Filament\Resources\PostResource\Pages;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
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

class PostResource extends Resource
{
    use HasLocalizedNavigation;

    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = Post::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Posts';

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Article Info')
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

                                Forms\Components\Select::make('author_id')
                                    ->label('Author Account')
                                    ->options(fn (): array => User::query()
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                Forms\Components\TextInput::make('author_name')
                                    ->label('Author Name')
                                    ->helperText('Fallback nama penulis jika tidak memilih akun author.')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\Textarea::make('excerpt')
                                    ->label('Excerpt')
                                    ->rows(4)
                                    ->nullable()
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Content')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Content')
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
                            ->columnSpanFull(),
                    ]),

                Section::make('Category & Tags')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->label('Category')
                                    ->options(fn (): array => Category::query()
                                        ->where('type', 'post')
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
                                                    ->orWhereIn('type', ['general', 'post']);
                                            })
                                            ->orderBy('name')
                                    )
                                    ->getOptionLabelFromRecordUsing(fn (Tag $record): string => $record->name)
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ]),

                Section::make('Publishing')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->required()
                                    ->options([
                                        'draft' => 'Draft',
                                        'review' => 'Review',
                                        'published' => 'Published',
                                        'archived' => 'Archived',
                                    ])
                                    ->default('draft'),

                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('Published At')
                                    ->native(false)
                                    ->nullable(),

                                Forms\Components\DateTimePicker::make('scheduled_at')
                                    ->label('Scheduled At')
                                    ->native(false)
                                    ->nullable(),

                                Forms\Components\TextInput::make('reading_time')
                                    ->label('Reading Time')
                                    ->suffix('min')
                                    ->numeric()
                                    ->minValue(0)
                                    ->nullable(),

                                Forms\Components\TextInput::make('views')
                                    ->label('Views')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Featured')
                                    ->helperText('Centang untuk menandai post sebagai unggulan.')
                                    ->default(false),
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

                                ImageUploadService::configureFilamentUpload(
                                    Forms\Components\FileUpload::make('og_image')
                                        ->label('OG Image'),
                                    'posts/og'
                                )
                                    ->imageEditor()
                                    ->imagePreviewHeight(180)
                                    ->nullable()
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Media')
                    ->schema([
                        ImageUploadService::configureFilamentUpload(
                            Forms\Components\FileUpload::make('featured_image')
                                ->label('Featured Image'),
                            'posts/featured'
                        )
                            ->imageEditor()
                            ->nullable()
                            ->imagePreviewHeight(250)
                            ->columnSpanFull(),

                        ImageUploadService::configureFilamentUpload(
                            Forms\Components\FileUpload::make('thumbnail')
                                ->label('Legacy Thumbnail'),
                            'posts'
                        )
                            ->helperText('Dipertahankan untuk kompatibilitas konten lama.')
                            ->imageEditor()
                            ->nullable()
                            ->imagePreviewHeight(200)
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('mediaItems')
                            ->label('Gallery Images')
                            ->relationship('mediaItems')
                            ->schema([
                                ImageUploadService::configureFilamentUpload(
                                    Forms\Components\FileUpload::make('file_path')
                                        ->label('Image'),
                                    'posts/gallery'
                                )
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
                Tables\Columns\ImageColumn::make('display_image')
                    ->label('Image')
                    ->disk('public')
                    ->rounded()
                    ->square()
                    ->height(56)
                    ->width(56),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable()
                    ->placeholder(fn (Post $record): string => $record->author_name ?: '-')
                    ->limit(30),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'review' => 'Review',
                        'published' => 'Published',
                        'archived' => 'Archived',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'review' => 'warning',
                        'published' => 'success',
                        'archived' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Scheduled')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('views')
                    ->label('Views')
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
                        'draft' => 'Draft',
                        'review' => 'Review',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
            ])
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
