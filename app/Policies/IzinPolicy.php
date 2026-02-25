<?php

namespace App\Policies;

use App\Models\Izin;
use App\Models\User;

class IzinPolicy
{
    /**
     * Siapa yang bisa melihat daftar Izin karyawan?
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['boss', 'owner', 'admin']);
    }

    /**
     * Siapa yang bisa melihat detail form Izin (termasuk foto surat dokter)?
     */
    public function view(User $user, Izin $izin): bool
    {
        return in_array($user->role, ['boss', 'owner', 'admin']);
    }

    /**
     * Siapa yang bisa input Izin manual dari Dashboard?
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['boss', 'owner', 'admin']);
    }

    /**
     * Siapa yang bisa MENGUBAH STATUS Izin (Approve/Reject)?
     */
    public function update(User $user, Izin $izin): bool
    {
        return in_array($user->role, ['boss', 'owner', 'admin']);
    }

    /**
     * 🔒 GUARD: Siapa yang boleh MENGHAPUS pengajuan Izin?
     * Hanya Boss & Owner.
     */
    public function delete(User $user, Izin $izin): bool
    {
        return in_array($user->role, ['boss', 'owner']);
    }

    public function deleteAny(User $user): bool
    {
        return in_array($user->role, ['boss', 'owner']);
    }
}