<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class ActivityPolicy
{
    /**
     * Siapa yang bisa melihat daftar log?
     */
    public function viewAny(User $user): bool
    {
        // 🔥 HANYA OWNER
        return $user->role === 'owner';
    }

    /**
     * Siapa yang bisa melihat detail log tertentu?
     */
    public function view(User $user, Activity $activity): bool
    {
        // 🔥 HANYA OWNER
        return $user->role === 'owner';
    }

    /**
     * Log tidak boleh dibuat manual, diubah, atau dihapus (Read-Only)
     */
    public function create(User $user): bool { return false; }
    public function update(User $user, Activity $activity): bool { return false; }
    public function delete(User $user, Activity $activity): bool { return false; }
    public function deleteAny(User $user): bool { return false; }
}