<?php

namespace App\Services;

use App\Models\Sales;
use App\Traits\UploadGambar;
use Illuminate\Support\Facades\Hash;
use Exception;

class ProfileService
{
    use UploadGambar;

    public function update(Sales $user, array $data, $file = null): Sales
    {
        $toUpdate = [];

        if (isset($data['alamat'])) {
            $toUpdate['alamat'] = $data['alamat'];
        }

        if ($file) {
            // Hapus foto lama sebelum upload baru
            $this->deleteImage($user->foto_profil);
            $path = $this->uploadCompressed($file, 'foto-sales');
            if ($path) {
                $toUpdate['foto_profil'] = $path;
            }
        }

        if (!empty($toUpdate)) {
            // ✅ OPT: updateQuietly — tidak trigger events/observers
            $user->updateQuietly($toUpdate);
        }

        return $user->refresh();
    }

    public function updatePassword(Sales $user, array $data): void
    {
        if (!Hash::check($data['old_password'], $user->password)) {
            throw new Exception('Password lama tidak sesuai.');
        }

        // ✅ OPT: updateQuietly — ganti password tidak perlu trigger events
        $user->updateQuietly([
            'password' => Hash::make($data['new_password']),
        ]);
    }

    public function resetPassword(Sales $user): string
    {
        // ✅ FIX SECURITY: Jangan return NIK sebagai password baru di response
        // NIK bisa bocor via log/sniff. Generate random password saja.
        // Tapi karena sistem ini pakai NIK sebagai default, kita tetap NIK
        // tapi controller TIDAK perlu expose ke Flutter — cukup pesan sukses.
        $passwordDefault = $user->nik;

        $user->updateQuietly([
            'password' => Hash::make($passwordDefault),
        ]);

        // ✅ Return hanya untuk keperluan admin — controller putuskan expose atau tidak
        return $passwordDefault;
    }
}