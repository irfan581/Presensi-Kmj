<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->unique();
            $table->string('nama')->index(); 
            $table->string('no_hp')->nullable();
            $table->string('area')->nullable();
            $table->text('alamat')->nullable();
            $table->string('password');
            $table->string('foto_profil')->nullable();
            $table->string('device_id')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};