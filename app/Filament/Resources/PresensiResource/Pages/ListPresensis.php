<?php

namespace App\Filament\Resources\PresensiResource\Pages;

use App\Filament\Resources\PresensiResource;
use Filament\Resources\Pages\ListRecords;

class ListPresensis extends ListRecords
{
    protected static string $resource = PresensiResource::class;

    // CreateAction dihapus — konsisten dengan canCreate(): false di resource
    // Data presensi hanya masuk via API Mobile (Laravel Sanctum)
    protected function getHeaderActions(): array
    {
        return [];
    }
}