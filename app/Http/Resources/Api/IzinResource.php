<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IzinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // ✅ Kolom yang ada di DB
            'id'             => $this->id,
            'sales_id'       => $this->sales_id,
            'tanggal'        => $this->tanggal?->format('Y-m-d'),
            'sampai_tanggal' => $this->sampai_tanggal?->format('Y-m-d'),
            'jenis_izin'     => $this->jenis_izin,
            'keterangan'     => $this->keterangan,
            'bukti_foto_url' => $this->bukti_foto_url,
            'status'         => $this->status,
            'alasan_tolak'   => $this->alasan_tolak,
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),

            // ✅ Computed (accessor di Model, tidak dari DB)
            'status_label'   => $this->status_label,
            'durasi_hari'    => $this->durasi_hari,
        ];
    }
}