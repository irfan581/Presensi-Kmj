<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, LogsActivity;

    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'role',              
        'is_admin', 
        'allowed_ip', 
        'session_version',
        'avatar_url',
        'permissions', // ✅ Tambahkan ini Bang
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
            'permissions' => 'array', // ✅ Paksa jadi Array biar bisa dicentang-centang
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return (new LogOptions())
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $allowedRoles = ['owner', 'boss', 'admin'];
        return in_array($this->role, $allowedRoles) || $this->is_admin === true;
    }

    public function getFilamentAvatarUrl(): ?string 
    { 
        return $this->avatar_url ? Storage::url($this->avatar_url) : null; 
    }

    public static function passwordRules(): Password 
    {
        return Password::min(12)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();
    }

    public function bumpSessionVersion(): void 
    {
        $this->increment('session_version');
    }
}