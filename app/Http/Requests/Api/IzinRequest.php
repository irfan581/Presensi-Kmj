<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class IzinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal'        => ['required', 'date', 'date_format:Y-m-d'],
            'sampai_tanggal' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:tanggal'],
            'jenis_izin'     => ['required', 'string', 'in:izin,sakit,cuti,terlambat,pulang_cepat'],
            'keterangan'     => ['required', 'string', 'min:3', 'max:500'],
            'bukti_foto'     => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required'              => 'Tanggal izin wajib diisi',
            'tanggal.date_format'           => 'Tanggal harus format YYYY-MM-DD',
            'sampai_tanggal.after_or_equal' => 'Tanggal akhir tidak boleh mendahului tanggal mulai',
            'jenis_izin.required'           => 'Jenis izin wajib dipilih',
            'jenis_izin.in'                 => 'Jenis izin tidak valid',
            'keterangan.required'           => 'Keterangan wajib diisi',
            'keterangan.min'                => 'Keterangan minimal 3 karakter',
            'keterangan.max'                => 'Keterangan maksimal 500 karakter',
            'bukti_foto.image'              => 'File harus berupa gambar',
            'bukti_foto.mimes'              => 'Format gambar harus jpg, jpeg, atau png',
            'bukti_foto.max'                => 'Ukuran gambar maksimal 5MB',
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sales_id'       => $this->user()?->id,
            'sampai_tanggal' => $this->sampai_tanggal ?? $this->tanggal,
        ]);
    }
}