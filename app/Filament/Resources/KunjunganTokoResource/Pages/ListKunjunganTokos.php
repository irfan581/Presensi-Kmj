<?php

namespace App\Filament\Resources\KunjunganTokoResource\Pages;

use App\Filament\Resources\KunjunganTokoResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListKunjunganTokos extends ListRecords
{
    protected static string $resource = KunjunganTokoResource::class;

    // Tidak ada header actions — input hanya via mobile app
    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $model = static::getResource()::getModel();

        return [
            'semua' => Tab::make('Semua Kunjungan')
                ->badge($model::count()),

            'aman' => Tab::make('Valid')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_suspicious', false))
                ->badge($model::where('is_suspicious', false)->count())
                ->badgeColor('success'),

            'curang' => Tab::make('Indikasi Fake GPS')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_suspicious', true))
                ->badge($model::where('is_suspicious', true)->count())
                ->badgeColor('danger'),
        ];
    }

    // Filament v3: records per page dikontrol via $table->recordsPerPageSelectOptions()
    // di KunjunganTokoResource::table(), bukan di sini. Contoh:
    // ->recordsPerPageSelectOptions([10, 25, 50, 100])
}