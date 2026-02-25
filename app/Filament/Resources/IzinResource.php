<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IzinResource\Pages;
use App\Models\Izin;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Illuminate\Support\Facades\Cache;

class IzinResource extends Resource
{
    protected static ?string $model = Izin::class;
    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Persetujuan Izin';
    protected static ?string $pluralModelLabel = 'Izin Karyawan';
    protected static ?int    $navigationSort  = 4;

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }

    public static function getNavigationBadge(): ?string
    {
        $count = Cache::remember('izin_pending_count', 60, function () {
            return Izin::query()->where('status', 'pending')->count();
        });
        return $count > 0 ? (string) $count : null;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Pengaju')
                    ->schema([
                        TextEntry::make('sales.nama')
                            ->label('Nama Sales')
                            ->weight('bold')
                            ->color('primary'),
                        TextEntry::make('status')
                            ->label('Status Saat Ini')
                            ->badge()
                            ->color(fn(string $state) => match ($state) {
                                'pending'   => 'warning',
                                'disetujui' => 'success',
                                'ditolak'   => 'danger',
                                default     => 'gray',
                            }),
                        TextEntry::make('tanggal')
                            ->label('Dari Tanggal')
                            ->date('d F Y'),
                        TextEntry::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->date('d F Y')
                            ->placeholder('Hanya 1 hari'),
                        TextEntry::make('durasi_hari')
                            ->label('Total Durasi')
                            ->suffix(' Hari')
                            ->weight('bold'),
                        TextEntry::make('jenis_izin')
                            ->label('Jenis Izin')
                            ->badge(),
                    ])->columns(3),

                Section::make('Lampiran & Alasan')
                    ->schema([
                        TextEntry::make('keterangan')
                            ->label('Alasan dari Sales')
                            ->columnSpanFull(),
                        ImageEntry::make('bukti_foto')
                            ->label('Foto Lampiran (Klik untuk memperbesar)')
                            ->disk('public')
                            ->url(fn (Izin $record) => $record->bukti_foto
                                ? asset('storage/' . $record->bukti_foto) : null)
                            ->openUrlInNewTab()
                            ->extraImgAttributes([
                                'class' => 'rounded-xl shadow-lg border border-gray-200 hover:scale-105 transition-transform duration-300 cursor-zoom-in',
                                'style' => 'max-height: 500px; width: auto; object-fit: contain; background-color: #f9fafb;',
                            ])
                            ->placeholder('Tidak ada lampiran foto'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with('sales'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('sales.nama')
                    ->label('Nama Sales')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tgl Mulai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('durasi_hari')
                    ->label('Durasi')
                    ->suffix(' Hari')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('jenis_izin')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'pending'   => 'warning',
                        'disetujui' => 'success',
                        'ditolak'   => 'danger',
                        default     => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Review'),

                Tables\Actions\Action::make('setujui')
                    ->label('ACC')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->hidden(fn(Izin $record) => $record->status !== 'pending')
                    ->action(function (Izin $record) {
                        $record->status = 'disetujui';
                        $record->save();
                        Cache::forget('izin_pending_count');
                        Notification::make()->success()->title('Izin Disetujui')->send();
                    }),

                Tables\Actions\Action::make('tolak')
                    ->label('Tolak')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->hidden(fn(Izin $record) => $record->status !== 'pending')
                    ->action(function (Izin $record) {
                        $record->status = 'ditolak';
                        $record->save();
                        Cache::forget('izin_pending_count');
                        Notification::make()->danger()->title('Izin Ditolak')->send();
                    }),

                // ✅ Tombol delete di table row
                Tables\Actions\Action::make('hapus')
                    ->label('Hapus')
                    ->color('gray')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Data Izin')
                    ->modalDescription('Data izin ini akan dihapus permanen.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->action(function (Izin $record) {
                        $record->delete();
                        Cache::forget('izin_pending_count');
                        Notification::make()->success()->title('Data Izin Dihapus')->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIzins::route('/'),
            'view'  => Pages\ViewIzin::route('/{record}'),
        ];
    }
}