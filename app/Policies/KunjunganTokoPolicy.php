<?php

namespace App\Policies;

use App\Models\User;
use App\Models\KunjunganToko;

class KunjunganTokoPolicy
{
    /**
     * Siapa saja yang bisa melihat tabel kunjungan.
     */
    public function viewAny(User $user): bool
    {
        // Owner, Boss, dan Admin semua boleh lihat daftar
        return in_array($user->role, ['owner', 'boss', 'admin']);
    }

    /**
     * Siapa yang bisa melihat detail kunjungan (View).
     */
    public function view(User $user, KunjunganToko $kunjunganToko): bool
    {
        return in_array($user->role, ['owner', 'boss', 'admin']);
    }

    /**
     * Siapa yang bisa mengedit (misal ganti status suspicious).
     */
    public function update(User $user, KunjunganToko $kunjunganToko): bool
    {
        // Owner, Boss, dan Admin boleh update data/status
        return in_array($user->role, ['owner', 'boss', 'admin']);
    }

    /**
     * Siapa yang bisa menghapus satu data.
     */
    public function delete(User $user, KunjunganToko $kunjunganToko): bool
    {
        // HANYA OWNER yang boleh hapus
        return $user->role === 'owner';
    }

    /**
     * Siapa yang bisa menghapus banyak data sekaligus (Bulk Action).
     */
    public function deleteAny(User $user): bool
    {
        // HANYA OWNER yang boleh hapus massal
        return $user->role === 'owner';
    }
}