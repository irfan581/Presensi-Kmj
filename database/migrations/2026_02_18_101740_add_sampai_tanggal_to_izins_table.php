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
    Schema::table('izins', function (Blueprint $table) {
        // Kita letakkan setelah kolom tanggal agar rapi
        $table->date('sampai_tanggal')->nullable()->after('tanggal');
    });
}

public function down(): void
{
    Schema::table('izins', function (Blueprint $table) {
        $table->dropColumn('sampai_tanggal');
    });
}
};