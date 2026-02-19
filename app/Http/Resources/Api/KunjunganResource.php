<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KunjunganResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            // ✅ FIX: Tambah sales_id agar konsisten dengan PresensiResource
            'sales_id'  => $this->sales_id,
            'nama_toko' => $this->nama_toko,
            'lokasi'    => $this->location,
            
            // Full URL dari accessor (sudah difix di model)
            'foto_kunjungan' => $this->foto_kunjungan_url,

            'keterangan' => $this->keterangan,

            // Jam & Tanggal dengan timezone Jakarta
            'jam' => $this->created_at
                ? $this->created_at->timezone('Asia/Jakarta')->format('H:i')
                : now('Asia/Jakarta')->format('H:i'),

            'tanggal' => $this->created_at
                ? $this->created_at->timezone('Asia/Jakarta')->format('Y-m-d')
                : now('Asia/Jakarta')->format('Y-m-d'),

            // URL Google Maps
            'google_maps_url' => $this->location
                ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($this->location)
                : null,

            // ✅ FIX: Expose is_suspicious & suspicious_reason
            // Sekarang $hidden dikosongkan di model → Resource bisa akses
            'is_suspicious'    => $this->is_suspicious,
            'suspicious_reason' => $this->suspicious_reason,

            'dibuat_pada' => $this->created_at
                ? $this->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i')
                : now('Asia/Jakarta')->format('d-m-Y H:i'),
        ];
    }
}