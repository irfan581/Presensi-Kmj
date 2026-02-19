<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Izin extends Model
{
    use HasFactory;

    protected $table = 'izins';

    protected $fillable = [
        'sales_id',
        'tanggal',
        'sampai_tanggal', 
        'jenis_izin',
        'keterangan',
        'bukti_foto', 
        'status',
        'alasan_tolak',      // Kolom baru dari Bos
        'rejection_reason',   // Kolom cadangan dari Bos
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'sampai_tanggal' => 'date',
        'approved_at' => 'datetime',
    ];

    // Ini kunci agar Flutter Bos langsung dapat data yang dibutuhkan
    protected $appends = ['status_label', 'durasi_hari', 'bukti_foto_url'];

    // ═══════════════════════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════════════════════

    public function getStatusLabelAttribute(): string
    {
        // Sesuaikan dengan ENUM di DB: pending, disetujui, ditolak
        return match ($this->status) {
            'pending'   => 'Menunggu Approval',
            'disetujui' => 'Disetujui',
            'ditolak'   => 'Ditolak',
            default     => ucfirst($this->status ?? ''),
        };
    }

    public function getDurasiHariAttribute(): int
    {
        if (!$this->sampai_tanggal || $this->tanggal->equalTo($this->sampai_tanggal)) {
            return 1;
        }
        return $this->tanggal->diffInDays($this->sampai_tanggal) + 1;
    }

    // Accessor untuk Flutter (ModelIzin Bos butuh 'bukti_foto_url')
    public function getBuktiFotoUrlAttribute(): ?string
    {
        if (empty($this->bukti_foto)) return null;
        
        // Jika isinya URL lengkap (misal dari s3/hosting lain)
        if (filter_var($this->bukti_foto, FILTER_VALIDATE_URL)) {
            return $this->bukti_foto;
        }
        
        // Mengirim URL absolut agar Flutter bisa langsung menampilkan gambar
        return asset(Storage::url($this->bukti_foto));
    }

    // ═══════════════════════════════════════════════════════════
    // RELASI & SCOPES
    // ═══════════════════════════════════════════════════════════

    public function sales(): BelongsTo
    {
        return $this->belongsTo(Sales::class, 'sales_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeApproved($query) { return $query->where('status', 'disetujui'); }
    public function scopeRejected($query) { return $query->where('status', 'ditolak'); }
}