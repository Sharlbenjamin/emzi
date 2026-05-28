<?php

namespace App\Filament\Resources\ProductSetResource\Pages;

use App\Filament\Resources\ProductSetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductSet extends EditRecord
{
    protected static string $resource = ProductSetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
