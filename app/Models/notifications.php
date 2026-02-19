<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class notifications extends Model
{
    /**
     * Nama tabel di database
     */
    protected $table = 'notifications';

    /**
     * Laravel Notifications menggunakan UUID (string), bukan Integer
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Kolom yang bisa diisi
     */
    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    /**
     * Cast kolom 'data' otomatis menjadi array/object
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Relasi ke user (siapa yang menerima notif)
     */
    public function notifiable()
    {
        return $this->morphTo();
    }
}