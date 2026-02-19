<?php

namespace App\Jobs;

use App\Models\Presensi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessPresensi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $backoff = 10;

    protected array $data;
    protected ?int  $presensiId;

    public function __construct(array $data, ?int $presensiId = null)
    {
        $this->data       = $data;
        $this->presensiId = $presensiId;
    }

    public function handle(): void
    {
        try {
            if (!$this->presensiId) {
                Log::warning('ProcessPresensi: presensiId kosong, job dibatalkan.');
                return;
            }

            /** @var Presensi|null $presensi */
            $presensi = Presensi::find($this->presensiId);

            if (!$presensi instanceof Presensi) {
                Log::warning("ProcessPresensi: Presensi ID {$this->presensiId} tidak ditemukan.");
                return;
            }

            // ✅ FIX Intelephense P1013:
            // Gunakan @var di atas + instanceof check agar Intelephense
            // tahu $presensi pasti Presensi (bukan Presensi|null)
            $updatedData = $this->movePhotos($this->data);

            $presensi->update($updatedData);

            Log::info("ProcessPresensi: Foto diproses untuk ID {$this->presensiId}.");

        } catch (\Exception $e) {
            Log::error('ProcessPresensi gagal: ' . $e->getMessage());
            throw $e;
        }
    }

    private function movePhotos(array $data): array
    {
        foreach (['foto_masuk', 'foto_pulang'] as $column) {
            if (!isset($data[$column])) continue;
            if (!str_contains($data[$column], 'temp-absen/')) continue;

            $tempPath = $data[$column];
            $newPath  = str_replace('temp-absen/', 'absen-sales/', $tempPath);

            if (Storage::disk('public')->exists($tempPath)) {
                Storage::disk('public')->move($tempPath, $newPath);
                $data[$column] = $newPath;
                Log::info("ProcessPresensi: Foto dipindahkan → {$newPath}");
            } else {
                Log::warning("ProcessPresensi: File tidak ditemukan di {$tempPath}");
            }
        }

        return $data;
    }
}