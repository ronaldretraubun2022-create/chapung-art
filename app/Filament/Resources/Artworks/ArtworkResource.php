<?php

namespace App\Filament\Resources\Artworks;

use App\Filament\Concerns\HasLocalizedNavigation;
use App\Filament\Resources\Artworks\Pages\CreateArtwork;
use App\Filament\Resources\Artworks\Pages\EditArtwork;
use App\Filament\Resources\Artworks\Pages\ListArtworks;
use App\Filament\Resources\Artworks\Schemas\ArtworkForm;
use App\Filament\Resources\Artworks\Tables\ArtworksTable;
use App\Models\Artwork;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ArtworkResource extends Resource
{
    use HasLocalizedNavigation;

    protected static bool $shouldCheckPolicyExistence = false;

    protected static ?string $model = Artwork::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Artworks';

    protected static ?string $modelLabel = 'Artwork';

    protected static ?string $pluralModelLabel = 'Artworks';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ArtworkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArtworksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArtworks::route('/'),
            'create' => CreateArtwork::route('/create'),
            'edit' => EditArtwork::route('/{record}/edit'),
        ];
    }
}
