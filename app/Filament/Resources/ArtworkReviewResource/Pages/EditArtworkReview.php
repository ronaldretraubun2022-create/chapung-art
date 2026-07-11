<?php

namespace App\Filament\Resources\ArtworkReviewResource\Pages;

use App\Filament\Resources\ArtworkReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArtworkReview extends EditRecord
{
    protected static string $resource = ArtworkReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
