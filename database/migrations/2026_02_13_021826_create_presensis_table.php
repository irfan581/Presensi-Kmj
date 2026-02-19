<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presensis', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sales_id')->constrained('sales')->onDelete('cascade');
    $table->date('tanggal');
    $table->time('jam_masuk'); // Waktu server
    $table->string('status', 50)->nullable();
    
    // Gunakan string jika data dari HP berupa jam + menit + detik (kadang ada milidetik)
    $table->string('jam_perangkat_masuk')->nullable(); 
    
    $table->time('jam_pulang')->nullable(); // Waktu server
    $table->string('jam_perangkat_pulang')->nullable();
    
    $table->string('foto_masuk');
    $table->string('foto_pulang')->nullable();
    
    // Menggunakan text atau string panjang untuk koordinat GPS (lat,long)
    $table->string('location_masuk');
    $table->string('location_pulang')->nullable();
    
    $table->text('keterangan')->nullable();
    $table->boolean('is_suspicious')->default(false);
    $table->string('suspicious_reason')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};