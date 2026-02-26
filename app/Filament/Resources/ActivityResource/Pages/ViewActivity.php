<?php

namespace App\Filament\Resources\ActivityResource\Pages;

use App\Filament\Resources\ActivityResource;
use Filament\Resources\Pages\ViewRecord;

class ViewActivity extends ViewRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Log bersifat read-only, jadi kita tidak butuh tombol Edit atau Delete di sini
        ];
    }

    /**
     * Mengatur Judul Halaman Detail
     */
    public function getTitle(): string 
    {
        return "Detail Log Aktivitas #{$this->record->id}";
    }
}