<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteSettingResource\Pages;
use App\Models\SiteSetting;
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

class SiteSettingResource extends Resource
{
    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = SiteSetting::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?string $modelLabel = 'Site Setting';
    protected static ?string $pluralModelLabel = 'Site Settings';
    protected static ?string $recordTitleAttribute = 'key';
    protected static string|UnitEnum|null $navigationGroup = 'CMS';
    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Setting')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->label('Key')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->rule('regex:/^[a-z0-9_\-]+$/')
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Select::make('type')
                                    ->label('Type')
                                    ->options([
                                        'text' => 'Text',
                                        'textarea' => 'Textarea',
                                        'url' => 'URL',
                                        'email' => 'Email',
                                        'phone' => 'Phone',
                                        'image' => 'Image',
                                    ])
                                    ->default('text')
                                    ->required()
                                    ->live()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\TextInput::make('group')
                                    ->label('Group')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(['default' => 2, 'md' => 1]),

                                Forms\Components\Textarea::make('value')
                                    ->label('Value')
                                    ->rows(5)
                                    ->nullable()
                                    ->visible(fn (callable $get): bool => $get('type') !== 'image')
                                    ->columnSpan(2),

                                ImageUploadService::configureFilamentUpload(
                                    Forms\Components\FileUpload::make('value')
                                        ->label('Image Value'),
                                    'site-settings'
                                )
                                    ->imagePreviewHeight(120)
                                    ->nullable()
                                    ->visible(fn (callable $get): bool => $get('type') === 'image')
                                    ->columnSpan(2),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('group')
                    ->label('Group')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->limit(55)
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options(fn (): array => SiteSetting::query()
                        ->whereNotNull('group')
                        ->orderBy('group')
                        ->pluck('group', 'group')
                        ->all()),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'text' => 'Text',
                        'textarea' => 'Textarea',
                        'url' => 'URL',
                        'email' => 'Email',
                        'phone' => 'Phone',
                        'image' => 'Image',
                    ]),
            ])
            ->emptyStateHeading('Belum ada site setting')
            ->emptyStateDescription('Tambahkan setting untuk brand, kontak, media sosial, dan konfigurasi frontend.')
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('group');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiteSettings::route('/'),
            'create' => Pages\CreateSiteSetting::route('/create'),
            'edit' => Pages\EditSiteSetting::route('/{record}/edit'),
        ];
    }
}
