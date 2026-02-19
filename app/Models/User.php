<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\Rules\Password;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',            
        'is_admin',
        'allowed_ip',
        'session_version',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'session_version' => 'integer',
            'role' => 'string',
        ];
    }

    /**
     * KUNCI LOGIN FILAMENT
     * Cek apakah user punya role 'admin' atau is_admin bernilai true.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Pastikan role di DB tulisannya 'admin' (huruf kecil semua)
        return ($this->role === 'admin' || $this->is_admin === true);
    }

    /**
     * AVATAR SETTINGS
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return null;
    }

    /**
     * PASSWORD RULES
     */
    public static function passwordRules(): Password
    {
        return Password::min(12)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();
    }

    /**
     * HELPER CHECK ADMIN
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || (bool) $this->is_admin;
    }

    /**
     * SESSION SECURITY
     */
    public function bumpSessionVersion(): void
    {
        $this->increment('session_version');
    }
}