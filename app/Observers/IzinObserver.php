<?php

namespace App\Observers;

use App\Models\Izin;
use App\Models\User;
use App\Models\NotifikasiSales; // Menggunakan model custom untuk riwayat Flutter
use App\Jobs\ProcessIzin;
use App\Services\FcmService;
use Filament\Notifications\Notification as FilamentNotification; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class IzinObserver
{
    public function created(Izin $izin): void
    {
        if ($izin->bukti_foto) {
            ProcessIzin::dispatch($izin->id, $izin->bukti_foto);
        }
        $nama = $izin->sales->nama ?? 'Sales';
        $admins = User::where('is_admin', true)->orWhere('role', 'admin')->get();

        if ($admins->isNotEmpty()) {
            FilamentNotification::make()
                ->title('Pengajuan Izin Baru')
                ->body("**{$nama}** mengajukan izin {$izin->jenis_izin}.")
                ->warning()
                ->icon('heroicon-o-document-text')
                ->sendToDatabase($admins);
        }
        Cache::forget('izin_pending_count');
    }
    public function updated(Izin $izin): void
    {
        
        if ($izin->wasChanged('status')) {
            $sales = $izin->sales;

            if ($sales) {
                $isApproved = $izin->status === 'disetujui';
                $statusColor = $isApproved ? 'success' : 'danger';
                $statusLabel = $isApproved ? 'DISETUJUI ✅' : 'DITOLAK ❌';
                $alasan = $izin->alasan_tolak ? "\nAlasan: {$izin->alasan_tolak}" : "";
                $tglFormatted = $izin->tanggal ? $izin->tanggal->format('d/m/Y') : '-';
                $pesan = "Pengajuan izin {$izin->jenis_izin} Anda untuk tanggal {$tglFormatted} telah **{$statusLabel}**.{$alasan}";

                FilamentNotification::make()
                    ->title('Update Status Izin')
                    ->body($pesan)
                    ->{$statusColor}()
                    ->icon($isApproved ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->sendToDatabase($sales);
                NotifikasiSales::create([
                    'sales_id' => $izin->sales_id,
                    'title'    => "Status Izin: {$statusLabel}",
                    'message'  => $pesan,
                    'is_read'  => false,
                ]);

                
                $token = $sales->fcm_token ?? $sales->device_id;
                if ($token) {
                    FcmService::sendNotification(
                        $token,
                        "Status Izin: {$statusLabel}",
                        strip_tags(str_replace('**', '', $pesan)) 
                    );
                }
            }
            Cache::forget('izin_pending_count');
        }
    }

    public function deleted(Izin $izin): void
    {
        if ($izin->bukti_foto) {
            Storage::disk('public')->delete($izin->bukti_foto);
        }

        Cache::forget('izin_pending_count');
    }
}