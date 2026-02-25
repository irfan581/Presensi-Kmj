<?php

namespace App\Services;

use App\Models\Izin;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class IzinService
{
    public function getIzinAktif(int $salesId, ?string $tanggal = null): ?Izin
    {
        $tgl = $tanggal ?? Carbon::now('Asia/Jakarta')->toDateString();

        return Izin::where('sales_id', $salesId)
            ->whereIn('status', ['approved', 'disetujui'])
            ->where('tanggal', '<=', $tgl)
            ->where('sampai_tanggal', '>=', $tgl)
            ->latest()
            ->first();
    }

    public function cekBolehAbsenMasuk(int $salesId): array
    {
        $izin = $this->getIzinAktif($salesId);

        if (!$izin) {
            return ['boleh' => true, 'alasan' => null, 'izin' => null];
        }

        $jenis = strtolower($izin->jenis_izin);

        if (in_array($jenis, ['sakit', 'cuti'])) {
            return [
                'boleh'  => false,
                'alasan' => "Anda sedang izin {$izin->jenis_izin} hingga " .
                            Carbon::parse($izin->sampai_tanggal)->format('d M Y'),
                'izin'   => $izin,
            ];
        }

        return ['boleh' => true, 'alasan' => null, 'izin' => $izin];
    }

    public function tentukanStatusAbsen(int $salesId, string $jamMasuk): string
    {
        $izin     = $this->getIzinAktif($salesId);
        $batasJam = config('absensi.batas_jam', env('ABSENSI_BATAS_JAM', '08:00'));

        if ($izin && strtolower($izin->jenis_izin) === 'terlambat') {
            return 'terlambat_izin';
        }

        try {
            $masuk = Carbon::createFromFormat('H:i:s', $jamMasuk);
            $batas = Carbon::createFromFormat('H:i', $batasJam);
            return $masuk->gt($batas) ? 'terlambat' : 'tepat_waktu';
        } catch (\Exception $e) {
            return 'tepat_waktu';
        }
    }

    public function bolehPulangCepat(int $salesId): bool
    {
        $izin = $this->getIzinAktif($salesId);
        return $izin && strtolower($izin->jenis_izin) === 'pulang_cepat';
    }

    public function getRiwayatIzin(
        int $salesId,
        ?string $status = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): Collection {
        $query = Izin::where('sales_id', $salesId)->latest();

        if ($status)    $query->where('status', $status);
        if ($startDate) $query->whereDate('tanggal', '>=', $startDate);
        if ($endDate)   $query->whereDate('tanggal', '<=', $endDate);

        return $query->get();
    }

    public function ajukanIzin(array $data, $fileBukti = null): Izin
    {
        $tglMulai   = Carbon::parse($data['tanggal']);
        $tglSelesai = Carbon::parse($data['sampai_tanggal'] ?? $data['tanggal']);

        // ✅ Hanya insert kolom yang ada di DB
        $izin = Izin::create([
            'sales_id'       => $data['sales_id'],
            'tanggal'        => $tglMulai->toDateString(),
            'sampai_tanggal' => $tglSelesai->toDateString(),
            'jenis_izin'     => $data['jenis_izin'],
            'keterangan'     => $data['keterangan'] ?? null,
            'status'         => 'pending',
        ]);

        // ✅ Upload foto — jika gagal, izin tetap tersimpan
        if ($fileBukti) {
            try {
                $path = $fileBukti->store('bukti-izin', 'public');
                $izin->update(['bukti_foto' => $path]);
                Log::info("Foto izin #{$izin->id} tersimpan: {$path}");
            } catch (\Exception $e) {
                Log::error("Gagal upload foto izin #{$izin->id}: " . $e->getMessage());
            }
        }

        return $izin->refresh();
    }
}