<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'nik'  => $this->nik,
            'nama' => $this->nama,
            'role' => $this->role,
            'no_hp'  => $this->no_hp,
            'alamat' => $this->alamat,
            'area'   => $this->area,
            'foto_profil_url' => $this->foto_profil_url,
            'is_active'   => (bool) $this->is_active,
            'status_akun' => $this->is_active ? 'Aktif' : 'Non-Aktif',
        ];
    }
}