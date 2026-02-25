<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // ✅ FIX: Cek field yang ada di request, bukan route name
        // Lebih reliable karena tidak bergantung pada nama route yang bisa typo
        if ($this->has('old_password') || $this->has('new_password')) {
            return $this->changePasswordRules();
        }

        if ($this->routeIs('profile.reset-password')) {
            return [];
        }

        return $this->updateProfileRules();
    }

    private function updateProfileRules(): array
    {
        return [
            'foto_profil' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'alamat'      => 'nullable|string|max:500',
        ];
    }

    private function changePasswordRules(): array
    {
        return [
            'old_password'              => 'required|string',
            'new_password'              => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'old_password.required'              => 'Password lama wajib diisi.',
            'new_password.required'              => 'Password baru wajib diisi.',
            'new_password.min'                   => 'Password minimal 8 karakter.',
            'new_password.confirmed'             => 'Konfirmasi password baru tidak cocok.',
            'new_password_confirmation.required' => 'Konfirmasi password wajib diisi.',
            'foto_profil.image'                  => 'File harus berupa gambar.',
            'foto_profil.mimes'                  => 'Foto harus format JPG atau PNG.',
            'foto_profil.max'                    => 'Ukuran foto maksimal 2MB.',
            'alamat.max'                         => 'Alamat maksimal 500 karakter.',
        ];
    }

    // Tangkap error validasi dan return JSON
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
        $toTrim = ['old_password', 'new_password', 'new_password_confirmation', 'alamat'];
        $trimmed = [];
        foreach ($toTrim as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $trimmed[$field] = trim($this->input($field));
            }
        }
        if (!empty($trimmed)) {
            $this->merge($trimmed);
        }
    }
}