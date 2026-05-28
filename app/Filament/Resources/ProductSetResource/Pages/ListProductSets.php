<?php

namespace App\Filament\Resources\ProductSetResource\Pages;

use App\Filament\Resources\ProductSetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductSets extends ListRecords
{
    protected static string $resource = ProductSetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
