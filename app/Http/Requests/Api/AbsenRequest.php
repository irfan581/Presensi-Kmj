<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AbsenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Fungsi ini akan berjalan SEBELUM validasi dimulai.
     * Tujuannya untuk memecah string "-7.72,111.53" menjadi latitude & longitude.
     */
    protected function prepareForValidation()
    {
        // Ambil dari location_masuk atau location_pulang (mana yang diisi oleh Flutter)
        $location = $this->location_masuk ?? $this->location_pulang;

        if ($location && str_contains($location, ',')) {
            $coords = explode(',', $location);
            $this->merge([
                'latitude'  => trim($coords[0] ?? null),
                'longitude' => trim($coords[1] ?? null),
            ]);
        }
    }

    public function rules(): array
    {
        $isMasuk = $this->is('api/absen-masuk');

        return [
            // Sekarang latitude & longitude akan terisi otomatis dari string location
            'latitude'      => 'required|numeric',
            'longitude'     => 'required|numeric',
            'jam_perangkat' => 'nullable',
            'keterangan'    => 'nullable|string|max:255',
            
            // Validasi foto sesuai route
            'foto_masuk'    => $isMasuk ? 'required|image|mimes:jpeg,png,jpg|max:5120' : 'nullable',
            'foto_pulang'   => !$isMasuk ? 'required|image|mimes:jpeg,png,jpg|max:5120' : 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required'   => 'Lokasi GPS (Latitude) tidak terdeteksi.',
            'longitude.required'  => 'Lokasi GPS (Longitude) tidak terdeteksi.',
            'foto_masuk.required' => 'Foto absen masuk wajib diunggah.',
            'foto_pulang.required'=> 'Foto absen pulang wajib diunggah.',
            'foto_masuk.image'    => 'File harus berupa gambar.',
            'foto_masuk.max'      => 'Ukuran foto maksimal 5MB.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors()
        ], 422));
    }
}