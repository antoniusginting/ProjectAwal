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
        if (!Schema::hasTable('laporan_lumbung_records')) {
            Schema::create('laporan_lumbung_records', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('laporan_lumbung_id')->constrained('laporan_lumbungs')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_lumbung_records');
    }
};
