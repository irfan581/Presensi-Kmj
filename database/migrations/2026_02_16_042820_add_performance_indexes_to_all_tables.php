<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ─── PRESENSIS ────────────────────────────────────────────
        Schema::table('presensis', function (Blueprint $table) {

            if (!$this->indexExists('presensis', 'idx_presensis_sales_tanggal')) {
                $table->index(['sales_id', 'tanggal'], 'idx_presensis_sales_tanggal');
            }
            if (!$this->indexExists('presensis', 'idx_presensis_tanggal_desc')) {
                $table->index(['tanggal', 'id'], 'idx_presensis_tanggal_desc');
            }
        });

        // ─── KUNJUNGAN TOKOS ──────────────────────────────────────
        Schema::table('kunjungan_tokos', function (Blueprint $table) {

            if (!$this->indexExists('kunjungan_tokos', 'idx_kunjungan_sales_created')) {
                $table->index(['sales_id', 'created_at'], 'idx_kunjungan_sales_created');
            }
            if (!$this->indexExists('kunjungan_tokos', 'idx_kunjungan_sales_nama')) {
                $table->index(['sales_id', 'nama_toko'], 'idx_kunjungan_sales_nama');
            }
        });

        // ─── SALES ────────────────────────────────────────────────
        Schema::table('sales', function (Blueprint $table) {
            if (!$this->indexExists('sales', 'idx_sales_nik')) {
                $table->index('nik', 'idx_sales_nik');
            }
            if (!$this->indexExists('sales', 'idx_sales_is_active')) {
                $table->index('is_active', 'idx_sales_is_active');
            }
        });

        // ─── IZINS ────────────────────────────────────────────────
        Schema::table('izins', function (Blueprint $table) {
            if (!$this->indexExists('izins', 'idx_izins_sales_status')) {
                $table->index(['sales_id', 'status'], 'idx_izins_sales_status');
            }
            if (!$this->indexExists('izins', 'idx_izins_status')) {
                $table->index('status', 'idx_izins_status');
            }
        });
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            if (!$this->indexExists('personal_access_tokens', 'idx_tokens_tokenable')) {
                $table->index(['tokenable_type', 'tokenable_id'], 'idx_tokens_tokenable');
            }
        });
    }

    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_presensis_sales_tanggal');
            $table->dropIndexIfExists('idx_presensis_tanggal_desc');
        });

        Schema::table('kunjungan_tokos', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_kunjungan_sales_created');
            $table->dropIndexIfExists('idx_kunjungan_sales_nama');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_sales_nik');
            $table->dropIndexIfExists('idx_sales_is_active');
        });

        Schema::table('izins', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_izins_sales_status');
            $table->dropIndexIfExists('idx_izins_status');
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_tokens_tokenable');
        });
    }
    private function indexExists(string $table, string $indexName): bool
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->contains($indexName);
    }
};