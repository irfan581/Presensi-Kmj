<?php

namespace App\Observers;

use App\Models\Izin;
use App\Models\User;
use App\Models\NotifikasiSales;
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

        // ✅ Notif ke ADMIN di Filament panel (bukan ke sales)
        $nama   = $izin->sales->nama ?? 'Sales';
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
        if (!$izin->wasChanged('status')) return;

        $sales = $izin->sales;
        if (!$sales) {
            Cache::forget('izin_pending_count');
            return;
        }

        $isApproved   = in_array($izin->status, ['disetujui', 'approved']);
        $statusLabel  = $isApproved ? 'DISETUJUI ✅' : 'DITOLAK ❌';
        $alasan       = $izin->alasan_tolak ? " Alasan: {$izin->alasan_tolak}" : "";
        $tglFormatted = $izin->tanggal ? $izin->tanggal->format('d/m/Y') : '-';
        $jenisLabel   = ucfirst($izin->jenis_izin);

        $judul = "Izin {$jenisLabel}: {$statusLabel}";
        $pesan = "Pengajuan izin {$jenisLabel} Anda untuk tanggal {$tglFormatted} telah {$statusLabel}.{$alasan}";
        $pesanBersih = strip_tags(str_replace(['**', '✅', '❌'], ['', '', ''], $pesan));

        // ✅ 1. Simpan ke notifikasi_sales (dibaca Flutter)
        NotifikasiSales::create([
            'sales_id' => $izin->sales_id,
            'title'    => $judul,
            'message'  => $pesanBersih,
            'is_read'  => false,
        ]);

        // ✅ 2. Push notifikasi ke HP via FCM
        $token = $sales->fcm_token ?? $sales->device_id ?? null;
        if ($token) {
            FcmService::sendNotification($token, $judul, $pesanBersih);
        }

        // ❌ DIHAPUS: FilamentNotification::sendToDatabase($sales)
        // → Ini yang bikin double notif di Flutter karena Flutter baca 2 tabel

        Cache::forget('izin_pending_count');
    }

    public function deleted(Izin $izin): void
    {
        if ($izin->bukti_foto) {
            Storage::disk('public')->delete($izin->bukti_foto);
        }
        Cache::forget('izin_pending_count');
    }
}