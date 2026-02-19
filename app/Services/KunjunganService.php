<?php

namespace App\Services;

use App\Jobs\ProcessKunjungan;
use App\Models\KunjunganToko;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class KunjunganService
{
    public function storeKunjungan(int $salesId, array $data, $file): KunjunganToko
    {
        // ✅ FIX KRITIS: Simpan ke DB dulu → dapat ID real
        // Sebelum: dispatch Job dulu → return dummy KunjunganToko tanpa id/created_at
        // Akibat: KunjunganResource $this->id = null, $this->created_at = null

        // 1. Simpan foto sementara
        $path = $file->store('temp-kunjungan', 'public');

        // 2. Deteksi Fake GPS
        $isSuspicious = ($data['is_mock_location'] ?? false)
                     || ($data['location'] === '0,0');
        $reason = $isSuspicious
            ? 'Terdeteksi Fake GPS atau koordinat tidak valid.'
            : null;

        // 3. Create record di DB → dapat model dengan id real
        $kunjungan = KunjunganToko::create([
            'sales_id'          => $salesId,
            'nama_toko'         => $data['nama_toko'],
            'location'          => $data['location'],
            'keterangan'        => $data['keterangan'] ?? null,
            'foto_kunjungan'    => $path,   // path temp dulu
            'is_suspicious'     => $isSuspicious,
            'suspicious_reason' => $reason,
        ]);

        // 4. Dispatch Job hanya untuk pindah foto temp → permanent
        // Job UPDATE path foto di record yang sudah ada
        ProcessKunjungan::dispatch($kunjungan->id, $path);

        // 5. Return model nyata dengan id dan created_at valid
        return $kunjungan;
    }

    public function getRiwayat(
        int     $salesId,
        ?string $tanggalMulai = null,
        ?string $tanggalAkhir = null,
    ): Collection {
        return KunjunganToko::where('sales_id', $salesId)
            ->select([
                'id', 'sales_id', 'nama_toko', 'location',
                'foto_kunjungan', 'keterangan',
                'is_suspicious', 'suspicious_reason', 'created_at',
            ])
            ->when($tanggalMulai, fn($q) => $q->whereDate('created_at', '>=', $tanggalMulai))
            ->when($tanggalAkhir, fn($q) => $q->whereDate('created_at', '<=', $tanggalAkhir))
            ->when(!$tanggalMulai && !$tanggalAkhir,
                // ✅ Default 90 hari jika tidak ada filter
                fn($q) => $q->where('created_at', '>=', now()->subDays(90))
            )
            ->latest()
            ->limit(180) // ✅ Hard limit cegah payload raksasa
            ->get();
    }
}