<?php

namespace App\Models;

use Filament\Panel;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\DatabaseNotification;

class Sales extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'sales';
    protected $fillable = ['nik', 'nama', 'no_hp', 'area', 'alamat', 'foto_profil', 'device_id', 'password', 'is_active', 'last_login_at', 'fcm_token'];
    protected $hidden = ['password', 'remember_token', 'device_id', 'deleted_at'];
    protected $casts = ['last_login_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean'];
    protected $appends = ['foto_profil_url'];

    public function getFotoProfilUrlAttribute(): string
    {
        if (empty($this->foto_profil)) {
            return 'https://ui-avatars.com/api/?name='.urlencode($this->nama).'&background=EA580C&color=fff&bold=true';
        }
        return Storage::url($this->foto_profil);
    }

    public function presensi(): HasMany { return $this->hasMany(Presensi::class, 'sales_id'); }
    
    public function notifications(): HasMany
    {
        return $this->hasMany(DatabaseNotification::class, 'notifiable_id')
            ->where('notifiable_type', self::class)
            ->orderBy('created_at', 'desc');
    }
    public function notifikasi()
{
    return $this->hasMany(NotifikasiSales::class, 'sales_id');
}

    public function updateLastLogin(): void { $this->forceFill(['last_login_at' => now()])->save(); }
    public function canAccessPanel(Panel $panel): bool { return false; }
}