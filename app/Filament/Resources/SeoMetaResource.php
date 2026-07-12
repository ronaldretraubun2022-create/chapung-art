<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocalizedNavigation;
use App\Filament\Resources\SeoMetaResource\Pages;
use App\Models\Artwork;
use App\Models\Category;
use App\Models\Photography;
use App\Models\Post;
use App\Models\SeoMeta;
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
use UnitEnum;

class SeoMetaResource extends Resource
{
    use HasLocalizedNavigation;

    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = SeoMeta::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-magnifying-glass-circle';

    protected static ?string $navigationLabel = 'SEO Manager';

    protected static ?string $modelLabel = 'SEO Meta';

    protected static ?string $pluralModelLabel = 'SEO Metas';

    protected static ?string $recordTitleAttribute = 'meta_title';

    protected static string|UnitEnum|null $navigationGroup = 'CMS';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Target')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('seoable_type')
                                    ->label('Content Type')
                                    ->options(self::seoableTypeOptions())
                                    ->searchable()
                                    ->live()
                                    ->nullable()
                                    ->afterStateUpdated(fn (callable $set): mixed => $set('seoable_id', null)),

                                Forms\Components\Select::make('seoable_id')
                                    ->label('Content')
                                    ->options(fn (callable $get): array => self::seoableOptions($get('seoable_type')))
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                Forms\Components\TextInput::make('route_name')
                                    ->label('Route Name')
                                    ->helperText('Opsional. Contoh: home, gallery, photography.index, media.index.')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(2),
                            ]),
                    ]),

                Section::make('Meta')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('meta_title')
                                    ->label('Meta Title')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(2),

                                Forms\Components\Textarea::make('meta_description')
                                    ->label('Meta Description')
                                    ->rows(4)
                                    ->nullable()
                                    ->columnSpan(2),

                                Forms\Components\Textarea::make('meta_keywords')
                                    ->label('Meta Keywords')
                                    ->rows(3)
                                    ->helperText('Pisahkan keyword dengan koma.')
                                    ->nullable()
                                    ->columnSpan(2),

                                ImageUploadService::configureFilamentUpload(
                                    Forms\Components\FileUpload::make('og_image')
                                        ->label('OG Image'),
                                    'seo/og-images'
                                )
                                    ->imageEditor()
                                    ->imagePreviewHeight(220)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('canonical_url')
                                    ->label('Canonical URL')
                                    ->url()
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Select::make('robots')
                                    ->label('Robots')
                                    ->options([
                                        'index, follow' => 'index, follow',
                                        'noindex, follow' => 'noindex, follow',
                                        'index, nofollow' => 'index, nofollow',
                                        'noindex, nofollow' => 'noindex, nofollow',
                                    ])
                                    ->default('index, follow')
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\KeyValue::make('schema_json')
                                    ->label('Schema JSON')
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
                Tables\Columns\TextColumn::make('meta_title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(45)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('route_name')
                    ->label('Route')
                    ->searchable()
                    ->badge()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('seoable_type')
                    ->label('Type')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('seoable_id')
                    ->label('Content ID')
                    ->placeholder('-'),

                Tables\Columns\ImageColumn::make('og_image')
                    ->label('OG')
                    ->disk('public')
                    ->rounded()
                    ->height(48)
                    ->width(48),

                Tables\Columns\TextColumn::make('robots')
                    ->label('Robots')
                    ->badge()
                    ->placeholder('index, follow'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('seoable_type')
                    ->options(self::seoableTypeOptions()),
            ])
            ->emptyStateHeading(__('admin.empty_states.seo_meta_heading'))
            ->emptyStateDescription(__('admin.empty_states.seo_meta_description'))
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeoMetas::route('/'),
            'create' => Pages\CreateSeoMeta::route('/create'),
            'edit' => Pages\EditSeoMeta::route('/{record}/edit'),
        ];
    }

    private static function seoableTypeOptions(): array
    {
        return [
            Artwork::class => 'Artwork',
            Photography::class => 'Photography',
            Post::class => 'Post',
            Category::class => 'Category',
        ];
    }

    private static function seoableOptions(?string $type): array
    {
        return match ($type) {
            Artwork::class => Artwork::query()->orderBy('title')->pluck('title', 'id')->all(),
            Photography::class => Photography::query()->orderBy('title')->pluck('title', 'id')->all(),
            Post::class => Post::query()->orderBy('title')->pluck('title', 'id')->all(),
            Category::class => Category::query()->orderBy('name')->pluck('name', 'id')->all(),
            default => [],
        };
    }
}
