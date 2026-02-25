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
use Filament\Infolists\Components\Group;

class ViewKunjunganToko extends ViewRecord
{
    protected static string $resource = KunjunganTokoResource::class;

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
                // --- SECTION ATAS: RINGKASAN UTAMA ---
                Section::make('Informasi Kunjungan')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('sales.nama')
                                ->label('Nama Sales')
                                ->weight('bold')
                                ->color('primary')
                                ->icon('heroicon-m-user'),
                            
                            TextEntry::make('nama_toko')
                                ->label('Nama Toko')
                                ->weight('bold')
                                ->icon('heroicon-m-shopping-bag'),
                            
                            TextEntry::make('created_at')
                                ->label('Waktu Kunjungan')
                                ->dateTime('d M Y, H:i')
                                ->icon('heroicon-m-clock'),
                        ]),
                    ]),

                // --- GRID TENGAH: LOKASI VS FOTO ---
                Grid::make(3)->schema([
                    
                    // Kolom Kiri: Detail & Lokasi (Span 2)
                    Group::make([
                        Section::make('Detail & Validasi Lokasi')
                            ->schema([
                                TextEntry::make('keterangan')
                                    ->label('Hasil Kunjungan / Keterangan')
                                    ->placeholder('Tidak ada catatan tambahan.')
                                    ->prose(),

                                Grid::make(2)->schema([
                                    TextEntry::make('location')
                                        ->label('Koordinat GPS')
                                        ->icon('heroicon-m-map-pin')
                                        ->iconColor('danger')
                                        ->color('primary')
                                        ->weight('bold')
                                        // Format URL yang lebih clean untuk Google Maps
                                        ->url(fn ($state) => $state ? "https://www.google.com/maps/search/?api=1&query={$state}" : null)
                                        ->openUrlInNewTab()
                                        ->helperText('Klik koordinat di atas untuk membuka Google Maps'),

                                    Group::make([
                                        IconEntry::make('is_suspicious')
                                            ->label('Status Keamanan GPS')
                                            ->boolean()
                                            ->trueIcon('heroicon-o-exclamation-triangle')
                                            ->falseIcon('heroicon-o-check-circle')
                                            ->trueColor('danger')
                                            ->falseColor('success'),

                                        TextEntry::make('suspicious_reason')
                                            ->label('Alasan Indikasi Kecurangan')
                                            ->color('danger')
                                            ->weight('medium')
                                            ->visible(fn ($record) => $record->is_suspicious),
                                    ]),
                                ]),
                            ]),
                    ])->columnSpan(2),

                    // Kolom Kanan: Bukti Foto (Span 1)
                    Group::make([
                        Section::make('Bukti Foto')
                            ->schema([
                                ImageEntry::make('foto_kunjungan')
                                    ->hiddenLabel()
                                    ->disk('public')
                                    ->visibility('public')
                                    ->height(300)
                                    ->extraImgAttributes([
                                        'class' => 'rounded-xl w-full object-cover shadow-lg border border-gray-200',
                                        'style' => 'cursor: zoom-in;',
                                        // Trigger buka gambar di tab baru saat diklik lewat atribut HTML
                                        'onclick' => "window.open(this.src, '_blank')"
                                    ])
                                    ->placeholder('Foto tidak tersedia'),
                            ]),
                    ])->columnSpan(1),
                ]),
            ]);
    }
}