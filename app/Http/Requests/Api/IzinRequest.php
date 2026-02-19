<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class IzinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth sudah dicek di middleware auth:sanctum
    }

    public function rules(): array
    {
        return [
            'tanggal'         => ['required', 'date', 'date_format:Y-m-d'],
            
            // ✅ TAMBAHAN: Validasi sampai_tanggal (Opsi B)
            // 'after_or_equal:tanggal' memastikan rentang tanggal logis
            'sampai_tanggal'  => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:tanggal'],
            
            'jenis_izin'      => ['required', 'string', 'in:izin,sakit,cuti,terlambat'], // Tambahkan terlambat jika ada
            'keterangan'      => ['required', 'string', 'min:10', 'max:500'],
            'bukti_foto'      => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'], // 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required'         => 'Tanggal izin wajib diisi',
            'tanggal.date'             => 'Format tanggal tidak valid',
            'tanggal.date_format'      => 'Tanggal harus format Y-m-d (YYYY-MM-DD)',
            'sampai_tanggal.date'      => 'Format tanggal akhir tidak valid',
            'sampai_tanggal.date_format' => 'Tanggal akhir harus format Y-m-d',
            'sampai_tanggal.after_or_equal' => 'Tanggal akhir tidak boleh mendahului tanggal mulai',
            'jenis_izin.required'      => 'Jenis izin wajib dipilih',
            'jenis_izin.in'            => 'Jenis izin harus: izin, sakit, cuti, atau terlambat',
            'keterangan.required'      => 'Keterangan wajib diisi',
            'keterangan.min'           => 'Keterangan minimal 10 karakter',
            'keterangan.max'           => 'Keterangan maksimal 500 karakter',
            'bukti_foto.image'         => 'File harus berupa gambar',
            'bukti_foto.mimes'         => 'Format gambar harus: jpg, jpeg, atau png',
            'bukti_foto.max'           => 'Ukuran gambar maksimal 5MB',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}