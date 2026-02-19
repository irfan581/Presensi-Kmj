<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IzinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'sales_id'       => $this->sales_id,
            'tanggal'        => $this->tanggal?->format('Y-m-d'), 
            'sampai_tanggal' => $this->sampai_tanggal?->format('Y-m-d'), 
            'durasi_hari'    => $this->durasi_hari,
            'jenis_izin'     => $this->jenis_izin,
            'keterangan'     => $this->keterangan,
            'status'         => $this->status,
            'status_label'   => $this->status_label,
            'alasan_tolak'   => $this->alasan_tolak,
            'bukti_foto_url' => $this->bukti_foto_url,
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}