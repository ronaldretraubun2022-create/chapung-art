<?php

namespace App\Filament\Resources\PhotographyResource\Pages;

use App\Filament\Resources\PhotographyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPhotographies extends ListRecords
{
    protected static string $resource = PhotographyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
