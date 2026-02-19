<?php

namespace App\Filament\Resources\IzinResource\Pages;

use App\Filament\Resources\IzinResource;
use App\Models\NotifikasiSales;
use App\Services\FcmService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon; // Tambahkan ini untuk handle tanggal yang aman

class ViewIzin extends ViewRecord
{
    protected static string $resource = IzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Setujui Izin')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'pending')
                ->action(function ($record) {
                    // Pastikan variabel tanggal aman diproses
                    $tgl = $record->tanggal instanceof Carbon ? $record->tanggal->format('d/m/Y') : Carbon::parse($record->tanggal)->format('d/m/Y');

                    $record->update([
                        'status' => 'disetujui',
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                    ]);

                    NotifikasiSales::create([
                        'sales_id' => $record->sales_id,
                        'title'    => 'Izin Disetujui ✅',
                        'message'  => "Pengajuan izin Anda untuk tanggal {$tgl} telah disetujui Admin.",
                        'is_read'  => false,
                    ]);

                    $fcmToken = $record->sales?->fcm_token;
                    if ($fcmToken) {
                        FcmService::sendNotification(
                            $fcmToken, 
                            'Izin Disetujui ✅', 
                            "Pengajuan izin Anda untuk tanggal {$tgl} telah disetujui Admin."
                        );
                    }

                    Cache::forget('izin_pending_count');
                    Notification::make()->title('Izin Telah Disetujui')->success()->send();
                }),

            Actions\Action::make('reject')
                ->label('Tolak')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn ($record) => $record->status === 'pending')
                ->form([
                    \Filament\Forms\Components\Textarea::make('alasan_tolak')
                        ->label('Alasan Penolakan')
                        ->placeholder('Tulis alasan penolakan untuk sales...')
                        ->required(),
                ])
                ->action(function ($record, array $data) {
                    $tgl = $record->tanggal instanceof Carbon ? $record->tanggal->format('d/m/Y') : Carbon::parse($record->tanggal)->format('d/m/Y');

                    $record->update([
                        'status' => 'ditolak',
                        'alasan_tolak' => $data['alasan_tolak'],
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                    ]);

                    NotifikasiSales::create([
                        'sales_id' => $record->sales_id,
                        'title'    => 'Izin Ditolak ❌',
                        'message'  => "Maaf, izin tanggal {$tgl} ditolak. Alasan: " . $data['alasan_tolak'],
                        'is_read'  => false,
                    ]);

                    $fcmToken = $record->sales?->fcm_token;
                    if ($fcmToken) {
                        FcmService::sendNotification(
                            $fcmToken, 
                            'Izin Ditolak ❌', 
                            "Maaf, pengajuan izin Anda untuk tanggal {$tgl} ditolak."
                        );
                    }

                    Cache::forget('izin_pending_count');
                    Notification::make()->title('Izin Telah Ditolak')->danger()->send();
                }),
        ];
    }
}