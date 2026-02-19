<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotifikasiSales extends Model
{
    // Kasih tau Laravel kalau nama tabelnya custom
    protected $table = 'notifikasi_sales';

    protected $fillable = [
        'sales_id',
        'title',
        'message',
        'is_read'
    ];

    // Relasi balik ke Sales
    public function sales(): BelongsTo
    {
        return $this->belongsTo(Sales::class);
    }
}