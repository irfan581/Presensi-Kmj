<?php

namespace App\Services;

use App\Jobs\ProcessIzin;
use App\Models\Izin;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class IzinService
{
    public function ajukanIzin(array $data, $file = null): Izin
    {
        $path = $file ? $file->store('temp-izin', 'public') : null;
        
        $izin = Izin::create([
            'sales_id'       => Auth::id(),
            'tanggal'        => $data['tanggal'],
            'sampai_tanggal' => $data['sampai_tanggal'] ?? null, 
            'jenis_izin'     => $data['jenis_izin'],
            'keterangan'     => $data['keterangan'],
            'bukti_foto'     => $path,   
            'status'         => 'pending',
        ]);

        // DIPINDAH KE SINI: Agar dispatch jalan sebelum return
        if ($path) {
            ProcessIzin::dispatch($izin->id, $path);
        }

        return $izin;
    }

    public function getRiwayatIzin(
        int     $salesId,
        ?string $status       = null,
        ?string $tanggalMulai = null,
        ?string $tanggalAkhir = null,
    ): Collection {
        return Izin::where('sales_id', $salesId)
            ->select([
                'id', 
                'sales_id', 
                'tanggal', 
                'sampai_tanggal', // Tambahkan ke select agar muncul di riwayat Flutter
                'jenis_izin',
                'keterangan', 
                'bukti_foto', 
                'status',
                'created_at',
            ])
            ->when($status,       fn($q) => $q->where('status', $status))
            ->when($tanggalMulai, fn($q) => $q->whereDate('tanggal', '>=', $tanggalMulai))
            ->when($tanggalAkhir, fn($q) => $q->whereDate('tanggal', '<=', $tanggalAkhir))
            ->latest()
            ->limit(90) 
            ->get();
    }
}