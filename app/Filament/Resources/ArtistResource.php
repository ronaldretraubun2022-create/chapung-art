<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtistResource\Pages;
use App\Models\Artist;
use App\Models\User;
use App\Services\ImageUploadService;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use UnitEnum;

class ArtistResource extends Resource
{
    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = Artist::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Artists';
    protected static ?string $modelLabel = 'Artist';
    protected static ?string $pluralModelLabel = 'Artists';
    protected static ?string $recordTitleAttribute = 'name';
    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';
    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Profile')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Seniman')
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

                                Forms\Components\Select::make('user_id')
                                    ->label('User Account')
                                    ->options(fn (): array => User::query()
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->helperText('Opsional, hubungkan seniman dengan akun user.')
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\DatePicker::make('birth_date')
                                    ->label('Tanggal Lahir')
                                    ->native(false)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                ImageUploadService::configureFilamentUpload(
                                    Forms\Components\FileUpload::make('photo')
                                        ->label('Foto Profil'),
                                    'artists'
                                )
                                    ->imageEditor()
                                    ->imagePreviewHeight(220)
                                    ->nullable()
                                    ->columnSpan(2),

                                Forms\Components\Textarea::make('bio')
                                    ->label('Bio')
                                    ->rows(5)
                                    ->nullable()
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Contact')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('origin_area')
                                    ->label('Daerah Asal')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\TextInput::make('city')
                                    ->label('Kota')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\TextInput::make('province')
                                    ->label('Provinsi')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\TextInput::make('country')
                                    ->label('Negara')
                                    ->required()
                                    ->default('Indonesia')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\TextInput::make('phone')
                                    ->label('Telepon')
                                    ->tel()
                                    ->maxLength(50)
                                    ->nullable(),

                                Forms\Components\TextInput::make('whatsapp')
                                    ->label('WhatsApp')
                                    ->tel()
                                    ->maxLength(50)
                                    ->nullable(),

                                Forms\Components\TextInput::make('instagram')
                                    ->label('Instagram')
                                    ->prefix('@')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\TextInput::make('facebook')
                                    ->label('Facebook')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\TextInput::make('website')
                                    ->label('Website')
                                    ->url()
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 3, 'md' => 1]),
                            ]),
                    ]),

                Section::make('Professional Info')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('specialization')
                                    ->label('Spesialisasi')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(2),

                                Forms\Components\Textarea::make('education')
                                    ->label('Pendidikan')
                                    ->rows(4)
                                    ->nullable(),

                                Forms\Components\Textarea::make('achievements')
                                    ->label('Prestasi')
                                    ->rows(4)
                                    ->nullable(),

                                Forms\Components\Textarea::make('exhibitions')
                                    ->label('Pameran')
                                    ->rows(4)
                                    ->nullable()
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Status')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Featured')
                                    ->helperText('Tampilkan seniman sebagai unggulan.')
                                    ->default(false),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->helperText('Nonaktifkan jika profil belum siap tampil.')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('user'))
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->disk('public')
                    ->rounded()
                    ->square()
                    ->height(56)
                    ->width(56),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->limit(36),

                Tables\Columns\TextColumn::make('origin_area')
                    ->label('Asal')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->limit(28),

                Tables\Columns\TextColumn::make('city')
                    ->label('Kota')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('specialization')
                    ->label('Spesialisasi')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->limit(32),

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

                Tables\Filters\SelectFilter::make('province')
                    ->label('Provinsi')
                    ->options(fn (): array => Artist::query()
                        ->whereNotNull('province')
                        ->orderBy('province')
                        ->pluck('province', 'province')
                        ->all()),
            ])
            ->emptyStateHeading('Belum ada artist')
            ->emptyStateDescription('Tambahkan profil seniman Chapung Art untuk mulai mengelola katalog kreator.')
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
            'index' => Pages\ListArtists::route('/'),
            'create' => Pages\CreateArtist::route('/create'),
            'edit' => Pages\EditArtist::route('/{record}/edit'),
        ];
    }
}
