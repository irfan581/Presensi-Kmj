<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class KunjunganToko extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_tokos';

    protected $fillable = [
        'sales_id', 'nama_toko', 'location',
        'lat', 'lng',
        'foto_kunjungan', 'keterangan',
        'is_suspicious', 'suspicious_reason',
    ];

    protected $hidden = [];

    protected $casts = [
        'is_suspicious' => 'boolean',
        'lat'           => 'float',
        'lng'           => 'float',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    protected $appends = ['foto_kunjungan_url'];

    // ===========================================================
    // ACCESSOR
    // ===========================================================

    public function getFotoKunjunganUrlAttribute(): ?string
    {
        if (empty($this->foto_kunjungan)) return null;
        if (str_starts_with($this->foto_kunjungan, 'http')) return $this->foto_kunjungan;

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        return $disk->url($this->foto_kunjungan);
    }

    // ===========================================================
    // RELASI
    // ===========================================================

    public function sales(): BelongsTo
    {
        return $this->belongsTo(Sales::class, 'sales_id');
    }

    // ===========================================================
    // SCOPES
    // ===========================================================

    public function scopeHariIni($query)
    {
        return $query->whereDate('created_at', now('Asia/Jakarta')->toDateString());
    }

    public function scopeBySales($query, $salesId)
    {
        return $query->where('sales_id', $salesId);
    }

    // booted() dihapus — pakai KunjunganTokoObserver
}