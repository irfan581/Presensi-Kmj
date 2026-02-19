<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProfileRequest — untuk update foto profil dan alamat
 * Endpoint: POST /api/update-profile
 */
class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // ✅ OPT: Pisahkan rules berdasarkan route name
        // Lebih eksplisit dan tidak bergantung pada URL string yang bisa berubah
        if ($this->routeIs('profile.change-password')) {
            return $this->changePasswordRules();
        }

        if ($this->routeIs('profile.reset-password')) {
            return []; // Reset tidak butuh input — pakai NIK sebagai password
        }

        // Default: update profile (foto/alamat)
        return $this->updateProfileRules();
    }

    private function updateProfileRules(): array
    {
        return [
            // ✅ OPT: Minimal 1 field harus diisi — cegah request kosong
            'foto_profil' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'alamat'      => 'nullable|string|max:500',
        ];
    }

    private function changePasswordRules(): array
    {
        return [
            'old_password'              => 'required|string',
            // ✅ OPT: new_password_confirmation adalah field dari Flutter
            // 'confirmed' auto-check field 'new_password_confirmation'
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

    // ✅ OPT: Trim whitespace dari input teks — cegah data kotor di DB
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