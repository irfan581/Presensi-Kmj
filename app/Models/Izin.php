<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Izin extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'izins';

    // ✅ Selaras dengan kolom DB:
    // id, sales_id, tanggal, sampai_tanggal, jenis_izin,
    // keterangan, bukti_foto, status, alasan_tolak, created_at, updated_at
    protected $fillable = [
        'sales_id',
        'tanggal',
        'sampai_tanggal',
        'jenis_izin',
        'keterangan',
        'bukti_foto',
        'status',
        'alasan_tolak',
    ];

    protected $casts = [
        'tanggal'        => 'date',
        'sampai_tanggal' => 'date',
    ];

    // ✅ durasi_hari dihitung dari tanggal, tidak disimpan di DB
    protected $appends = ['status_label', 'durasi_hari', 'bukti_foto_url'];

    // ─── ACTIVITY LOG ─────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return (new LogOptions())
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ─── ACCESSORS ────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Menunggu Approval',
            'approved'  => 'Disetujui',
            'rejected'  => 'Ditolak',
            'disetujui' => 'Disetujui',
            'ditolak'   => 'Ditolak',
            default     => ucfirst($this->status ?? ''),
        };
    }

    // ✅ Computed — dihitung dari selisih tanggal
    public function getDurasiHariAttribute(): int
    {
        if (!$this->sampai_tanggal || !$this->tanggal) return 1;
        if ($this->tanggal->equalTo($this->sampai_tanggal)) return 1;
        return $this->tanggal->diffInDays($this->sampai_tanggal) + 1;
    }

    public function getBuktiFotoUrlAttribute(): ?string
    {
        if (empty($this->bukti_foto)) return null;
        if (filter_var($this->bukti_foto, FILTER_VALIDATE_URL)) {
            return $this->bukti_foto;
        }
        return asset(Storage::url($this->bukti_foto));
    }

    // ─── RELASI ───────────────────────────────────────────────

    public function sales(): BelongsTo
    {
        return $this->belongsTo(Sales::class, 'sales_id');
    }

    // ─── SCOPES ───────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('status', ['approved', 'disetujui']);
    }

    public function scopeRejected($query)
    {
        return $query->whereIn('status', ['rejected', 'ditolak']);
    }

    // ─── HELPER ───────────────────────────────────────────────

    public function isAktifPada(string $tanggal): bool
    {
        return $this->tanggal->toDateString() <= $tanggal
            && $this->sampai_tanggal->toDateString() >= $tanggal
            && in_array($this->status, ['approved', 'disetujui']);
    }
}