<?php

namespace App\Filament\Resources\PresensiResource\Pages;

use App\Filament\Resources\PresensiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
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
                            TextEntry::make('sales.nama')
                                ->label('Nama Sales')
                                ->weight('bold'),
                            TextEntry::make('sales.area')
                                ->label('Area'),
                            TextEntry::make('tanggal')
                                ->label('Tanggal')
                                ->date('d M Y'),
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
                                })
                                ->formatStateUsing(fn ($state) => $state === 'tepat_waktu'
                                    ? 'Tepat Waktu' : 'Terlambat'),

                            TextEntry::make('jam_masuk')
                                ->label('Jam Masuk')
                                ->time('H:i'),

                            TextEntry::make('jam_pulang')
                                ->label('Jam Pulang')
                                ->time('H:i')
                                ->placeholder('Belum Absen Pulang'),
                        ]),

                        Grid::make(3)->schema([
                            TextEntry::make('jam_perangkat_masuk')
                                ->label('Jam HP (Masuk)')
                                ->time('H:i')
                                ->placeholder('-'),

                            TextEntry::make('location_masuk')
                                ->label('Lokasi Masuk')
                                ->placeholder('-')
                                ->copyable()
                                ->url(fn ($record) => $record->location_masuk
                                    ? "https://www.google.com/maps/search/?api=1&query={$record->location_masuk}"
                                    : null)
                                ->openUrlInNewTab(),

                            TextEntry::make('location_pulang')
                                ->label('Lokasi Pulang')
                                ->placeholder('-')
                                ->copyable()
                                ->url(fn ($record) => $record->location_pulang
                                    ? "https://www.google.com/maps/search/?api=1&query={$record->location_pulang}"
                                    : null)
                                ->openUrlInNewTab(),
                        ]),

                        IconEntry::make('is_suspicious')
                            ->label('Status GPS')
                            ->boolean()
                            ->trueIcon('heroicon-o-exclamation-triangle')
                            ->falseIcon('heroicon-o-check-circle')
                            ->trueColor('danger')
                            ->falseColor('success'),
                    ]),

                // ─── SECTION FOTO ─────────────────────────────────────────────
                Section::make('Bukti Foto')
                    ->description('Klik foto untuk membuka ukuran penuh di tab baru')
                    ->schema([
                        Grid::make(2)->schema([

                            // ✅ FIX 1: Pakai getStateUsing() → accessor foto_masuk_url
                            // Accessor sudah handle: temp-absen/ → null, storage path → full URL
                            ImageEntry::make('foto_masuk_url')
                                ->label('Foto Masuk')
                                ->height(220)
                                ->extraImgAttributes([
                                    'class' => 'rounded-xl object-cover w-full',
                                    'style' => 'cursor:zoom-in',
                                ])
                                // ✅ FIX 2: Klik foto → buka URL langsung di tab baru
                                // (ImageEntry->action() tidak stabil di Filament v3)
                                ->url(fn ($record) => $record->foto_masuk_url)
                                ->openUrlInNewTab()
                                ->placeholder('—'),

                            ImageEntry::make('foto_pulang_url')
                                ->label('Foto Pulang')
                                ->height(220)
                                ->extraImgAttributes([
                                    'class' => 'rounded-xl object-cover w-full',
                                    'style' => 'cursor:zoom-in',
                                ])
                                ->url(fn ($record) => $record->foto_pulang_url)
                                ->openUrlInNewTab()
                                ->placeholder('Belum ada foto pulang'),
                        ]),
                    ])
                    // ✅ FIX 3: Cek accessor _url bukan kolom mentah
                    ->visible(fn ($record) => $record->foto_masuk_url || $record->foto_pulang_url),
            ]);
    }
}