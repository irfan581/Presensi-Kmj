<?php

namespace App\Filament\Resources\KunjunganTokoResource\Pages;

use App\Filament\Resources\KunjunganTokoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Grid;

class ViewKunjunganToko extends ViewRecord
{
    protected static string $resource = KunjunganTokoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol biru "Buka Lokasi" udah gue hapus dari sini, Bang.
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Kunjungan')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('sales.nama')
                                ->label('Nama Sales')
                                ->weight('bold'),
                            TextEntry::make('nama_toko')
                                ->label('Nama Toko'),
                            TextEntry::make('created_at')
                                ->label('Waktu Kunjungan')
                                ->dateTime('d M Y, H:i'),
                        ]),

                        Grid::make(2)->schema([
                            // KLIK TEKS INI UNTUK BUKA MAPS
                            TextEntry::make('location')
                                ->label('Koordinat GPS')
                                ->icon('heroicon-m-map-pin')
                                ->iconColor('danger')
                                ->color('primary')
                                ->weight('bold')
                                ->url(fn ($state) => $state ? "https://www.google.com/maps/search/?api=1&query={$state}" : null)
                                ->openUrlInNewTab()
                                ->placeholder('-'),

                            TextEntry::make('keterangan')
                                ->label('Keterangan')
                                ->placeholder('-'),
                        ]),

                        Grid::make(2)->schema([
                            IconEntry::make('is_suspicious')
                                ->label('Status GPS')
                                ->boolean()
                                ->trueIcon('heroicon-o-exclamation-triangle')
                                ->falseIcon('heroicon-o-check-circle')
                                ->trueColor('danger')
                                ->falseColor('success'),

                            TextEntry::make('suspicious_reason')
                                ->label('Alasan Indikasi')
                                ->placeholder('-')
                                ->visible(fn ($record) => $record->is_suspicious),
                        ]),
                    ]),

                Section::make('Bukti Foto Kunjungan')
                    ->schema([
                        // KLIK FOTO UNTUK ZOOM
                        ImageEntry::make('foto_kunjungan')
                            ->disk('public')
                            ->visibility('public')
                            ->height(400)
                            ->extraImgAttributes(fn ($state) => [
                                'onclick' => $state ? "window.open('" . asset('storage/' . $state) . "', '_blank')" : null,
                                'style' => 'cursor: zoom-in;',
                                'class' => 'rounded-xl object-contain bg-gray-100 shadow-sm transition hover:opacity-80',
                            ]),
                    ])
                    ->visible(fn ($record) => !empty($record->foto_kunjungan)),
            ]);
    }
}