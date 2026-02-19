<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensis';

    protected $fillable = [
        'sales_id', 'tanggal',
        'jam_masuk', 'jam_perangkat_masuk', 'status',
        'foto_masuk', 'location_masuk', 'lat_masuk', 'lng_masuk',
        'jam_pulang', 'jam_perangkat_pulang',
        'foto_pulang', 'location_pulang', 'lat_pulang', 'lng_pulang',
        'keterangan', 'is_suspicious', 'suspicious_reason',
    ];

    protected $hidden = [];

    protected $casts = [
        'tanggal'       => 'date:Y-m-d',
        'is_suspicious' => 'boolean',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    protected $appends = [
        'foto_masuk_url',
        'foto_pulang_url',
        'status_label',
    ];

    // ===========================================================
    // ACCESSOR
    // ===========================================================

    private function buildFotoUrl(?string $path): ?string
    {
        if (empty($path)) return null;
        if (str_contains($path, 'temp-absen/')) return null;

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        return $disk->url($path);
    }

    public function getFotoMasukUrlAttribute(): ?string
    {
        return $this->buildFotoUrl($this->foto_masuk);
    }

    public function getFotoPulangUrlAttribute(): ?string
    {
        return $this->buildFotoUrl($this->foto_pulang);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'tepat_waktu' => 'Tepat Waktu',
            'terlambat'   => 'Terlambat',
            default       => '-',
        };
    }

    // ===========================================================
    // RELASI
    // ===========================================================

    public function sales(): BelongsTo
    {
        return $this->belongsTo(Sales::class, 'sales_id');
    }

    public function kunjungan(): HasMany
    {
        return $this->hasMany(KunjunganToko::class, 'sales_id', 'sales_id');
    }

    // ===========================================================
    // SCOPES
    // ===========================================================

    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal', now('Asia/Jakarta'));
    }

    public function scopeBySales($query, int $salesId)
    {
        return $query->where('sales_id', $salesId);
    }

    // booted() dihapus — pakai PresensiObserver
}