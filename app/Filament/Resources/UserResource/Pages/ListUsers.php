<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah User Baru')
                ->icon('heroicon-m-user-plus')
                ->color('primary'),
        ];
    }

    public function getTitle(): string 
    {
        return "Manajemen Pengguna";
    }

    // Tips: Biar tampilan tabel di HP lebih rapi
    public function getHeaderWidgets(): array
    {
        return [
        ];
    }
}