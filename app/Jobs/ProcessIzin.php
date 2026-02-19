<?php

namespace App\Jobs;

use App\Models\Izin;
use App\Models\Sales;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;

use function Symfony\Component\String\s;

class ProcessIzin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    public $backoff = 10;

    protected int $izinId;
    protected ?string $tempPath;
    public function __construct(int $izinId, ?string $tempPath = null)
    {
        $this->izinId = $izinId;
        $this->tempPath = $tempPath;
    }

    public function handle(): void
    {
        try {
            $izin = Izin::with('sales')->find($this->izinId);

            if (!$izin) {
                Log::warning("ProcessIzin: Record Izin ID {$this->izinId} tidak ditemukan.");
                return;
            }

            // 1. LOGIKA PEMINDAHAN FOTO (Hanya jika ada tempPath)
            if ($this->tempPath && str_contains($this->tempPath, 'temp-izin/')) {
                $this->handlePhotoMoving($izin);
            }

            // 2. LOGIKA PUSH NOTIFICATION FIREBASE
            // Dipicu jika status sudah berubah (Approved/Rejected)
            if ($izin->status !== 'pending') {
                $this->sendFirebaseNotification($izin);
            }

        } catch (\Exception $e) {
            Log::error("ProcessIzin Gagal pada ID {$this->izinId}: " . $e->getMessage());
            throw $e; // Throw agar queue tahu job ini perlu diretry
        }
    }

    /**
     * Pindahkan file dari temp ke folder permanen agar storage rapi
     */
    protected function handlePhotoMoving(Izin $izin): void
    {
        if (!Storage::disk('public')->exists($this->tempPath)) {
            Log::warning("ProcessIzin: File temp tidak ditemukan di {$this->tempPath}");
            return;
        }

        // Siapkan path baru
        $filename = basename($this->tempPath);
        $newPath = 'izin-bukti/' . $filename;

        // Pastikan folder tujuan ada
        if (!Storage::disk('public')->exists('izin-bukti')) {
            Storage::disk('public')->makeDirectory('izin-bukti');
        }

        // Pindahkan file
        if (Storage::disk('public')->move($this->tempPath, $newPath)) {
            // Update Database tanpa memicu Observer (mencegah infinite loop)
            $izin->updateQuietly([
                'bukti_foto' => $newPath
            ]);
            Log::info("ProcessIzin: Foto Izin ID {$this->izinId} berhasil dipindahkan.");
        }
    }

    /**
     * Kirim Push Notification via Firebase (FCM)
     */
    // ... (Bagian header tetap sama)
    protected function sendFirebaseNotification(Izin $izin): void
    {
        $sales = $izin->sales;
        if (!$sales || empty($sales->device_id)) return;

        try {
            $messaging = app(Messaging::class);
            // SINKRONISASI ENUM DB: disetujui, ditolak
            $statusEmoji = $izin->status === 'disetujui' ? '✅' : '❌';
            $statusText  = strtoupper($izin->status);
            $jenisIzin   = ucfirst($izin->jenis_izin);
            
            // Tambahkan Alasan Tolak ke Body Notif jika ada
            $alasan = $izin->alasan_tolak ? " Alasan: {$izin->alasan_tolak}" : "";

            $message = CloudMessage::fromArray([
                'token' => $sales->device_id,
                'notification' => [
                    'title' => "Update Izin {$jenisIzin} {$statusEmoji}",
                    'body'  => "Pengajuan izin Anda telah {$statusText}.{$alasan}",
                ],
                'data' => [
                    'id' => (string) $izin->id,
                    'type' => 'izin_status_update',
                    'status' => (string) $izin->status,
                ],
                // ... (Sisa config android tetap sama)
            ]);

            $messaging->send($message);
        } catch (\Exception $e) {
            Log::error("FCM Error: " . $e->getMessage());
        }
    }
}