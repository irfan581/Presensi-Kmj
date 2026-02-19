<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSales extends ListRecords
{
    protected static string $resource = SalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Sales')
                ->icon('heroicon-o-user-plus'),
        ];
    }

    /**
     * Menambahkan Tabs di atas tabel untuk filter cepat
     */
    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Sales'),
            'aktif' => Tab::make('Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(SalesResource::getEloquentQuery()->where('is_active', true)->count())
                ->badgeColor('success'),
            'non_aktif' => Tab::make('Non-Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(SalesResource::getEloquentQuery()->where('is_active', false)->count())
                ->badgeColor('danger'),
        ];
    }
}