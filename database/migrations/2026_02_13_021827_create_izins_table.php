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
        Schema::create('izins', function (Blueprint $table) {
    $table->id();
    // Catatan: Di SQL kamu relasinya ke 'users', tapi idealnya ke 'sales'. 
    // Saya sesuaikan ke 'sales' agar konsisten dengan sistem presensi.
    $table->foreignId('sales_id')->constrained('sales')->onDelete('cascade');
    $table->date('tanggal');
    $table->enum('jenis_izin', ['sakit', 'izin', 'cuti']);
    $table->text('keterangan');
    $table->string('bukti_foto')->nullable();
    $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('izins');
    }
};
