<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity; // ✅ Tambahan
use Spatie\Activitylog\LogOptions;          // ✅ Tambahan

class Presensi extends Model
{
    use HasFactory, LogsActivity; // ✅ Aktifkan Log

    protected $table = 'presensis';

    protected $fillable = [
        'sales_id', 'tanggal', 'jam_masuk', 'jam_perangkat_masuk', 'status',
        'foto_masuk', 'location_masuk', 'lat_masuk', 'lng_masuk',
        'jam_pulang', 'jam_perangkat_pulang', 'foto_pulang', 'location_pulang', 
        'lat_pulang', 'lng_pulang', 'keterangan', 'is_suspicious', 'suspicious_reason',
    ];

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

    // --- ACTIVITY LOG SETTINGS ---
    public function getActivitylogOptions(): LogOptions
    {
        return (new LogOptions())
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // --- ACCESSORS ---
    protected function getFotoMasukUrlAttribute(): ?string
    {
        return $this->buildFotoUrl($this->foto_masuk);
    }

    protected function getFotoPulangUrlAttribute(): ?string
    {
        return $this->buildFotoUrl($this->foto_pulang);
    }

    protected function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'tepat_waktu' => 'Tepat Waktu',
            'terlambat'   => 'Terlambat',
            default       => 'Belum Absen',
        };
    }

    // --- HELPER LOGIC ---
    private function buildFotoUrl(?string $path): ?string
    {
        if (!$path || str_contains($path, 'temp-absen/')) {
            return null;
        }
        return asset('storage/' . $path);
    }

    // --- RELATIONS ---
    public function sales(): BelongsTo
    {
        return $this->belongsTo(Sales::class, 'sales_id');
    }

    public function kunjungan(): HasMany
    {
        return $this->hasMany(KunjunganToko::class, 'sales_id', 'sales_id')
            ->whereColumn('tanggal', 'presensis.tanggal');
    }

    // --- SCOPES ---
    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal', now('Asia/Jakarta')->toDateString());
    }

    public function scopeBySales($query, int $salesId)
    {
        return $query->where('sales_id', $salesId);
    }
}