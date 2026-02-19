<?php

namespace App\Filament\Resources\IzinResource\Pages;

use App\Filament\Resources\IzinResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListIzins extends ListRecords
{
    protected static string $resource = IzinResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Data'),
            
            'pending' => Tab::make('Menunggu')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'pending'))
                ->badge(static::getResource()::getModel()::where('status', 'pending')->count())
                ->badgeColor('warning'),
                
            'disetujui' => Tab::make('Disetujui')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'approved')),
                
            'ditolak' => Tab::make('Ditolak')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'rejected')),
        ];
    }
}