<?php

namespace App\Services;

use App\Models\Sales;
use App\Traits\UploadGambar;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
            $this->deleteOldPhoto($user->foto_profil);
            
            // Upload foto baru
            $path = $this->uploadCompressed($file, 'foto-sales');
            if ($path) {
                $toUpdate['foto_profil'] = $path;
            }
        }

        if (!empty($toUpdate)) {
            $user->updateQuietly($toUpdate);
        }

        return $user->refresh();
    }

    public function updatePassword(Sales $user, array $data): void
    {
        if (!Hash::check($data['old_password'], $user->password)) {
            throw new Exception('Password lama tidak sesuai.');
        }

        $user->updateQuietly([
            'password' => Hash::make($data['new_password']),
        ]);
    }

    public function resetPassword(Sales $user): string
    {
        $passwordDefault = $user->nik;

        $user->updateQuietly([
            'password' => Hash::make($passwordDefault),
        ]);

        return $passwordDefault;
    }

    // ✅ ADDED: Method untuk hapus foto lama
    protected function deleteOldPhoto(?string $oldPath): void
    {
        // Skip jika path kosong atau URL eksternal (ui-avatars)
        if (!$oldPath || str_starts_with($oldPath, 'http')) {
            return;
        }

        $disk = Storage::disk('public');
        
        // Hapus file jika exists
        if ($disk->exists($oldPath)) {
            $disk->delete($oldPath);
        }
    }
}