<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocalizedNavigation;
use App\Filament\Resources\ExhibitionResource\Pages;
use App\Models\Artwork;
use App\Models\Exhibition;
use App\Models\Photography;
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

class ExhibitionResource extends Resource
{
    use HasLocalizedNavigation;

    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = Exhibition::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Exhibitions';

    protected static ?string $modelLabel = 'Exhibition';

    protected static ?string $pluralModelLabel = 'Exhibitions';

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Exhibition Info')
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
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->rows(5)
                                    ->nullable()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('location')
                                    ->label('Location')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(2),

                                Forms\Components\DatePicker::make('start_date')
                                    ->label('Start Date')
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\DatePicker::make('end_date')
                                    ->label('End Date')
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),
                            ]),
                    ]),

                Section::make('Media')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                self::imageUpload('poster', 'Poster', 'exhibitions/posters'),
                                self::imageUpload('banner', 'Banner', 'exhibitions/banners'),
                            ]),
                    ]),

                Section::make('Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('Exhibition Items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Select::make('item_type')
                                    ->label('Item Type')
                                    ->options([
                                        'artwork' => 'Artwork',
                                        'photography' => 'Photography',
                                    ])
                                    ->default('artwork')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (callable $set): mixed => $set('item_id', null)),

                                Forms\Components\Select::make('item_id')
                                    ->label('Item')
                                    ->options(fn (callable $get): array => self::itemOptions($get('item_type')))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Order')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => self::itemLabel($state))
                            ->addActionLabel('Tambah Item Pameran')
                            ->columnSpanFull(),
                    ]),

                Section::make('Status')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options(self::statusOptions())
                                    ->default('draft')
                                    ->required(),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Featured')
                                    ->default(false),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('poster')
                    ->label('Poster')
                    ->disk('public')
                    ->rounded()
                    ->height(56)
                    ->width(56),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->limit(32)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('End')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'published', 'ongoing' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('is_featured')
                    ->label('Featured')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Featured' : 'Regular')
                    ->color(fn (bool $state): string => $state ? 'warning' : 'gray')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(self::statusOptions()),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
            ])
            ->emptyStateHeading(__('admin.empty_states.exhibitions_heading'))
            ->emptyStateDescription(__('admin.empty_states.exhibitions_description'))
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
            'index' => Pages\ListExhibitions::route('/'),
            'create' => Pages\CreateExhibition::route('/create'),
            'edit' => Pages\EditExhibition::route('/{record}/edit'),
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'published' => 'Published',
            'ongoing' => 'Ongoing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    private static function itemOptions(?string $itemType): array
    {
        return match ($itemType) {
            'photography' => Photography::query()->orderBy('title')->pluck('title', 'id')->all(),
            default => Artwork::query()->orderBy('title')->pluck('title', 'id')->all(),
        };
    }

    private static function itemLabel(array $state): ?string
    {
        if (blank($state['item_id'] ?? null)) {
            return 'Exhibition item';
        }

        $type = $state['item_type'] ?? 'artwork';
        $title = self::itemOptions($type)[$state['item_id']] ?? null;

        return $title ? Str::headline($type).': '.$title : 'Exhibition item';
    }

    private static function imageUpload(string $field, string $label, string $directory): Forms\Components\FileUpload
    {
        return ImageUploadService::configureFilamentUpload(
            Forms\Components\FileUpload::make($field)
                ->label($label),
            $directory
        )
            ->imageEditor()
            ->imagePreviewHeight(220)
            ->nullable()
            ->columnSpan(['default' => 2, 'md' => 1]);
    }
}
