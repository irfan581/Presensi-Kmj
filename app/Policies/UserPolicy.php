<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Memeriksa apakah user adalah pengurus sistem yang sah.
     * Kita izinkan 'boss' dan 'owner' (karena lo owner-nya).
     */
    private function hasSystemAccess(User $user): bool
    {
        return in_array($user->role, ['boss', 'owner']);
    }

    /**
     * Menentukan siapa yang bisa melihat daftar user (Halaman Index).
     */
    public function viewAny(User $user): bool
    {
        return $this->hasSystemAccess($user);
    }

    /**
     * Menentukan siapa yang bisa melihat detail satu user.
     */
    public function view(User $user, User $model): bool
    {
        return $this->hasSystemAccess($user);
    }

    /**
     * Menentukan siapa yang bisa membuat user baru.
     */
    public function create(User $user): bool
    {
        return $this->hasSystemAccess($user);
    }

    /**
     * Menentukan siapa yang bisa mengubah data user.
     */
    public function update(User $user, User $model): bool
    {
        return $this->hasSystemAccess($user);
    }

    /**
     * Menentukan siapa yang bisa menghapus user.
     */
    public function delete(User $user, User $model): bool
    {
        // Boss/Owner bisa hapus siapa saja, KECUALI akunnya sendiri 
        // (Biar gak kekunci dari sistem)
        return $this->hasSystemAccess($user) && $user->id !== $model->id;
    }

    /**
     * Menentukan siapa yang bisa menghapus banyak user sekaligus.
     */
    public function deleteAny(User $user): bool
    {
        return $this->hasSystemAccess($user);
    }
}