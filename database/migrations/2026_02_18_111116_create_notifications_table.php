<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notifikasi_sales')) {
            Schema::create('notifikasi_sales', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sales_id')->constrained('sales')->onDelete('cascade');
                $table->string('title');
                $table->text('message');
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_sales');
    }
};
