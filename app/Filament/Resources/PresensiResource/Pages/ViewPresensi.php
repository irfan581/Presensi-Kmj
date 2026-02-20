<?php

namespace App\Filament\Resources\PresensiResource\Pages;

use App\Filament\Resources\PresensiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;

class ViewPresensi extends ViewRecord
{
    protected static string $resource = PresensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Sales')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('sales.nama')->label('Nama Sales')->weight('bold'),
                            TextEntry::make('sales.area')->label('Area'),
                            TextEntry::make('tanggal')->label('Tanggal')->date('d M Y'),
                        ]),
                    ]),

                Section::make('Data Kehadiran')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->color(fn ($state) => match ($state) {
                                    'tepat_waktu' => 'success',
                                    'terlambat'   => 'danger',
                                    default       => 'gray',
                                }),
                            TextEntry::make('jam_masuk')->label('Jam Masuk')->time('H:i'),
                            TextEntry::make('jam_pulang')->label('Jam Pulang')->time('H:i')->placeholder('Belum Pulang'),
                        ]),
                        
                        // --- FIX 1: LOKASI ---
                        // Langsung ambil nilainya ($state) karena koordinat lo udah gabung jadi satu
                        Grid::make(2)->schema([
                            TextEntry::make('location_masuk')
                                ->label('Lokasi Masuk')
                                ->icon('heroicon-m-map-pin')
                                ->iconColor('danger')
                                ->color('primary') 
                                ->weight('bold')
                                ->url(fn ($state) => $state ? "https://maps.google.com/?q={$state}" : null)
                                ->openUrlInNewTab()
                                ->placeholder('Lokasi tidak tersedia'),

                            TextEntry::make('location_pulang')
                                ->label('Lokasi Pulang')
                                ->icon('heroicon-m-map-pin')
                                ->iconColor('danger')
                                ->color('primary')
                                ->weight('bold')
                                ->url(fn ($state) => $state ? "https://maps.google.com/?q={$state}" : null)
                                ->openUrlInNewTab()
                                ->placeholder('Belum absen pulang'),
                        ]),
                    ]),

                // --- FIX 2: BUKTI FOTO ---
                // Pake Javascript murni 'onclick' di gambar. Mustahil meleset atau timeout!
                Section::make('Bukti Foto')
                    ->schema([
                        Grid::make(2)->schema([
                            
                            ImageEntry::make('foto_masuk')
                                ->label('Foto Masuk')
                                ->disk('public')
                                ->height(400)
                                ->extraImgAttributes(fn ($state) => [
                                    'onclick' => $state ? "window.open('" . asset('storage/' . $state) . "', '_blank')" : null,
                                    'style' => 'cursor: zoom-in;',
                                    'class' => 'rounded-xl object-contain bg-gray-100 shadow-sm transition hover:opacity-80',
                                ]),

                            ImageEntry::make('foto_pulang')
                                ->label('Foto Pulang')
                                ->disk('public')
                                ->height(400)
                                ->extraImgAttributes(fn ($state) => [
                                    'onclick' => $state ? "window.open('" . asset('storage/' . $state) . "', '_blank')" : null,
                                    'style' => 'cursor: zoom-in;',
                                    'class' => 'rounded-xl object-contain bg-gray-100 shadow-sm transition hover:opacity-80',
                                ])
                                ->placeholder('Belum ada foto pulang'),
                        ]),
                    ])
                    ->visible(fn ($record) => $record && ($record->foto_masuk || $record->foto_pulang)),
            ]);
    }
}