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

class ProcessIzin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $backoff = 10;

    protected int $izinId;
    protected ?string $tempPath;

    public function __construct(int $izinId, ?string $tempPath = null)
    {
        $this->izinId   = $izinId;
        $this->tempPath = $tempPath;
    }

    public function handle(): void
    {
        try {
            $izin = Izin::with('sales')->find($this->izinId);

            if (!$izin) {
                Log::warning("ProcessIzin: Izin ID {$this->izinId} tidak ditemukan.");
                return;
            }

            // 1. Pindahkan foto dari temp jika ada
            if ($this->tempPath && str_contains($this->tempPath, 'temp-izin/')) {
                $this->handlePhotoMoving($izin);
            }

            // 2. Kirim notifikasi jika sudah diproses (bukan pending)
            if ($izin->status !== 'pending') {
                $this->sendFirebaseNotification($izin);
            }

        } catch (\Exception $e) {
            Log::error("ProcessIzin gagal ID {$this->izinId}: " . $e->getMessage());
            throw $e;
        }
    }

    protected function handlePhotoMoving(Izin $izin): void
    {
        if (!Storage::disk('public')->exists($this->tempPath)) {
            Log::warning("ProcessIzin: File temp tidak ditemukan: {$this->tempPath}");
            return;
        }

        $filename = basename($this->tempPath);
        $newPath  = 'bukti-izin/' . $filename;

        if (!Storage::disk('public')->exists('bukti-izin')) {
            Storage::disk('public')->makeDirectory('bukti-izin');
        }

        if (Storage::disk('public')->move($this->tempPath, $newPath)) {
            // ✅ updateQuietly agar tidak trigger Observer → loop
            $izin->updateQuietly(['bukti_foto' => $newPath]);
            Log::info("ProcessIzin: Foto izin #{$izin->id} dipindahkan ke {$newPath}");
        }
    }

    protected function sendFirebaseNotification(Izin $izin): void
    {
        $sales = $izin->sales;
        if (!$sales || empty($sales->fcm_token)) return;

        try {
            $messaging    = app(Messaging::class);
            $statusEmoji  = in_array($izin->status, ['approved', 'disetujui']) ? '✅' : '❌';
            $statusText   = in_array($izin->status, ['approved', 'disetujui']) ? 'DISETUJUI' : 'DITOLAK';
            $jenisIzin    = ucfirst($izin->jenis_izin);
            $alasan       = $izin->alasan_tolak ? " Alasan: {$izin->alasan_tolak}" : "";

            $message = CloudMessage::fromArray([
                'token'        => $sales->fcm_token,
                'notification' => [
                    'title' => "Update Izin {$jenisIzin} {$statusEmoji}",
                    'body'  => "Pengajuan izin Anda telah {$statusText}.{$alasan}",
                ],
                'data' => [
                    'id'     => (string) $izin->id,
                    'type'   => 'izin_status_update',
                    'status' => (string) $izin->status,
                ],
                'android' => [
                    'priority' => 'high',
                ],
            ]);

            $messaging->send($message);
            Log::info("FCM izin #{$izin->id} terkirim ke sales #{$sales->id}");

        } catch (\Exception $e) {
            Log::error("FCM Error izin #{$izin->id}: " . $e->getMessage());
        }
    }
}