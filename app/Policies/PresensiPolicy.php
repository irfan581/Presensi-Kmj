<?php

namespace App\Policies;

use App\Models\Presensi;
use App\Models\User;

class PresensiPolicy
{
    /**
     * Siapa yang bisa melihat daftar Presensi di Dashboard?
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['boss', 'owner', 'admin']);
    }

    /**
     * Siapa yang bisa melihat detail Presensi?
     */
    public function view(User $user, Presensi $presensi): bool
    {
        return in_array($user->role, ['boss', 'owner', 'admin']);
    }

    /**
     * Siapa yang bisa membuat Presensi manual dari Dashboard?
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['boss', 'owner', 'admin']);
    }

    /**
     * Siapa yang bisa mengedit data Presensi (misal karyawan lupa absen)?
     */
    public function update(User $user, Presensi $presensi): bool
    {
        return in_array($user->role, ['boss', 'owner', 'admin']);
    }

    /**
     * 🔒 GUARD: Siapa yang boleh MENGHAPUS data Presensi?
     * Hanya Boss & Owner. Admin tidak boleh menghapus sejarah presensi.
     */
    public function delete(User $user, Presensi $presensi): bool
    {
        return in_array($user->role, ['boss', 'owner']);
    }

    public function deleteAny(User $user): bool
    {
        return in_array($user->role, ['boss', 'owner']);
    }
}