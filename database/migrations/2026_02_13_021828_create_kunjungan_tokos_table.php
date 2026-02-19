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
        Schema::create('kunjungan_tokos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sales_id')->constrained('sales')->onDelete('cascade');
    $table->string('nama_toko');
    $table->string('location');
    $table->string('foto_kunjungan');
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
        Schema::dropIfExists('kunjungan_tokos');
    }
};