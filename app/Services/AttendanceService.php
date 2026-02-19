<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\KunjunganToko;
use App\Jobs\ProcessPresensi;
use Carbon\Carbon;

class AttendanceService
{
    public function handleMasuk(int $salesId, array $data, $file): Presensi
    {
        $now = Carbon::now('Asia/Jakarta');
        if (!$file) throw new \Exception('File foto_masuk diperlukan.');

        if (Presensi::where('sales_id', $salesId)->where('tanggal', $now->format('Y-m-d'))->exists()) {
            throw new \Exception('Anda sudah absen masuk hari ini.');
        }

        $path = $file->store('temp-absen', 'public');
        $batas = (int) config('absensi.batas_jam_terlambat', 8);

        $presensi = Presensi::create([
            'sales_id'            => $salesId,
            'tanggal'             => $now->format('Y-m-d'),
            'jam_masuk'           => $now->format('H:i:s'),
            'status'              => $now->hour >= $batas ? 'terlambat' : 'tepat_waktu',
            'jam_perangkat_masuk' => $data['jam_perangkat_masuk'],
            'foto_masuk'          => $path,
            'location_masuk'      => $data['location_masuk'],
            'keterangan'          => $data['keterangan'] ?? null,
        ]);

        ProcessPresensi::dispatch(['foto_masuk' => $path], $presensi->id);
        return $presensi;
    }

    public function handlePulang(Presensi $presensi, array $data, $file): Presensi
    {
        $now = Carbon::now('Asia/Jakarta');
        if (!$file) throw new \Exception('File foto_pulang diperlukan.');

        $jumlahKunjungan = KunjunganToko::where('sales_id', $presensi->sales_id)
            ->whereDate('created_at', Carbon::today('Asia/Jakarta'))
            ->count();

        if ($jumlahKunjungan < 3) {
            throw new \Exception("Minimal 3 kunjungan toko (Baru {$jumlahKunjungan}).");
        }

        $path = $file->store('temp-absen', 'public');

        $presensi->update([
            'jam_pulang'           => $now->format('H:i:s'),
            'jam_perangkat_pulang' => $data['jam_perangkat_pulang'],
            'foto_pulang'          => $path,
            'location_pulang'      => $data['location_pulang'],
        ]);

        ProcessPresensi::dispatch(['foto_pulang' => $path], $presensi->id);
        return $presensi->fresh();
    }
}