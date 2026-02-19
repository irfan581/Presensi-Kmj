<?php

namespace App\Observers;

use App\Models\Izin;
use App\Models\User;
use App\Jobs\ProcessIzin;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class IzinObserver
{
    /**
     * Trigger saat Sales buat izin baru dari Flutter.
     */
    public function created(Izin $izin): void
    {
        // 1. Proses kompresi foto di background agar upload terasa cepat
        if ($izin->bukti_foto) {
            ProcessIzin::dispatch($izin->id, $izin->bukti_foto);
        }

        // 2. Notifikasi ke Dashboard Admin Filament
        $nama = $izin->sales->nama ?? 'Sales';
        $admins = User::where('is_admin', true)->orWhere('role', 'admin')->get();

        if ($admins->isNotEmpty()) {
            Notification::make()
                ->title('Pengajuan Izin Baru')
                ->body("**{$nama}** mengajukan izin {$izin->jenis_izin}.")
                ->warning()
                ->icon('heroicon-o-document-text')
                ->sendToDatabase($admins);
        }

        // 3. Reset cache dashboard (biar angka statistik update)
        Cache::forget('izin_pending_count');
    }

    /**
     * Trigger saat Admin klik ACC atau TOLAK di Filament.
     */
    public function updated(Izin $izin): void
    {
        // Hanya jalan jika statusnya berubah (disetujui/ditolak)
        if ($izin->wasChanged('status')) {
            $sales = $izin->sales;

            if ($sales) {
                $isApproved = $izin->status === 'disetujui';
                $statusColor = $isApproved ? 'success' : 'danger';
                $statusLabel = $isApproved ? 'DISETUJUI ✅' : 'DITOLAK ❌';
                
                // Ambil alasan dari kolom database terbaru
                $alasan = $izin->alasan_tolak ? "\nAlasan: {$izin->alasan_tolak}" : "";
                $pesan = "Izin {$izin->jenis_izin} untuk tanggal {$izin->tanggal->format('d/m/Y')} telah {$statusLabel}.{$alasan}";

                // Kirim notifikasi ke aplikasi Flutter (Database Notification)
                Notification::make()
                    ->title('Update Status Izin')
                    ->body($pesan)
                    ->{$statusColor}()
                    ->icon($isApproved ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->sendToDatabase($sales);

                // TIPS: Jika Bos sudah punya FCM, panggil fungsi kirim push notif di sini
            }

            Cache::forget('izin_pending_count');
        }
    }

    /**
     * Trigger saat data dihapus.
     */
    public function deleted(Izin $izin): void
    {
        // Hapus file agar storage tidak penuh sampah
        if ($izin->bukti_foto) {
            Storage::disk('public')->delete($izin->bukti_foto);
        }
        
        Cache::forget('izin_pending_count');
    }
}