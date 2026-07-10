<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificateResource\Pages;
use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Certificate;
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

class CertificateResource extends Resource
{
    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = Certificate::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Certificates';
    protected static ?string $modelLabel = 'Certificate';
    protected static ?string $pluralModelLabel = 'Certificates';
    protected static ?string $recordTitleAttribute = 'certificate_number';
    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';
    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Certificate')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('certificate_number')
                                    ->label('Certificate Number')
                                    ->helperText('Otomatis dibuat saat sertifikat disimpan jika dikosongkan.')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('Auto generated')
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('owner_name')
                                    ->label('Owner Name')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Select::make('artwork_id')
                                    ->label('Artwork')
                                    ->options(fn (): array => Artwork::query()
                                        ->orderBy('title')
                                        ->pluck('title', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (?int $state, callable $set): void {
                                        $artwork = $state ? Artwork::find($state) : null;

                                        if ($artwork?->artist_id) {
                                            $set('artist_id', $artwork->artist_id);
                                        }
                                    })
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Select::make('artist_id')
                                    ->label('Artist')
                                    ->options(fn (): array => Artist::query()
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\DatePicker::make('issued_at')
                                    ->label('Issued At')
                                    ->default(now())
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Toggle::make('is_verified')
                                    ->label('Verified')
                                    ->default(true)
                                    ->columnSpan(['default' => 2, 'md' => 1]),
                            ]),
                    ]),

                Section::make('Files & Verification')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\FileUpload::make('pdf_path')
                                    ->label('Certificate PDF')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(8192)
                                    ->disk('public')
                                    ->directory('certificates/pdfs')
                                    ->getUploadedFileNameForStorageUsing(fn ($file): string => Str::uuid().'.pdf')
                                    ->visibility('public')
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('qr_code_path')
                                    ->label('QR Verification Path')
                                    ->helperText('Path ini dapat dipakai sebagai target QR code.')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Placeholder::make('verification_url')
                                    ->label('Verification URL')
                                    ->content(fn (?Certificate $record): string => $record?->verification_url ?: 'Akan tersedia setelah sertifikat disimpan.')
                                    ->columnSpan(2),

                                Forms\Components\Textarea::make('notes')
                                    ->label('Notes')
                                    ->rows(4)
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
                Tables\Columns\TextColumn::make('certificate_number')
                    ->label('Certificate Number')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->copyable(),

                Tables\Columns\TextColumn::make('artwork.title')
                    ->label('Artwork')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('artist.name')
                    ->label('Artist')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('owner_name')
                    ->label('Owner')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Issued')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('is_verified')
                    ->label('Verified')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Verified' : 'Unverified')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('verification_url')
                    ->label('Verification URL')
                    ->state(fn (Certificate $record): string => $record->verification_url)
                    ->copyable()
                    ->limit(36),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Verified'),
            ])
            ->emptyStateHeading('Belum ada certificate')
            ->emptyStateDescription('Tambahkan certificate of authenticity untuk artwork.')
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
            'index' => Pages\ListCertificates::route('/'),
            'create' => Pages\CreateCertificate::route('/create'),
            'edit' => Pages\EditCertificate::route('/{record}/edit'),
        ];
    }
}
