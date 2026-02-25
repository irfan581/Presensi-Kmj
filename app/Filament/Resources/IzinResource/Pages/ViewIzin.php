<?php

namespace App\Filament\Resources\IzinResource\Pages;

use App\Filament\Resources\IzinResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ViewIzin extends ViewRecord
{
    protected static string $resource = IzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ─── SETUJUI ──────────────────────────────────
            Actions\Action::make('approve')
                ->label('Setujui Izin')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Setujui Pengajuan Izin')
                ->modalDescription('Tindakan ini akan memberitahu sales melalui notifikasi.')
                ->visible(fn ($record) => $record->status === 'pending')
                ->action(function ($record) {
                    // ✅ Cukup update status — Observer otomatis kirim notif & FCM
                    $record->update([
                        'status'      => 'disetujui',
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                    ]);

                    Cache::forget('izin_pending_count');

                    Notification::make()
                        ->title('Izin Telah Disetujui')
                        ->success()
                        ->send();
                }),

            // ─── TOLAK ────────────────────────────────────
            Actions\Action::make('reject')
                ->label('Tolak')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->modalHeading('Tolak Pengajuan Izin')
                ->visible(fn ($record) => $record->status === 'pending')
                ->form([
                    \Filament\Forms\Components\Textarea::make('alasan_tolak')
                        ->label('Alasan Penolakan')
                        ->placeholder('Tulis alasan penolakan untuk sales...')
                        ->required(),
                ])
                ->action(function ($record, array $data) {
                    // ✅ Cukup update status — Observer otomatis kirim notif & FCM
                    $record->update([
                        'status'       => 'ditolak',
                        'alasan_tolak' => $data['alasan_tolak'],
                        'approved_by'  => Auth::id(),
                        'approved_at'  => now(),
                    ]);

                    Cache::forget('izin_pending_count');

                    Notification::make()
                        ->title('Izin Telah Ditolak')
                        ->danger()
                        ->send();
                }),

            // ─── DELETE ───────────────────────────────────
            Actions\Action::make('delete')
                ->label('Hapus')
                ->color('gray')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('Hapus Pengajuan Izin')
                ->modalDescription('Data izin ini akan dihapus permanen dan tidak bisa dikembalikan.')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->action(function ($record) {
                    $record->delete();

                    Cache::forget('izin_pending_count');

                    Notification::make()
                        ->title('Izin Berhasil Dihapus')
                        ->success()
                        ->send();

                    $this->redirect(IzinResource::getUrl('index'));
                }),
        ];
    }
}