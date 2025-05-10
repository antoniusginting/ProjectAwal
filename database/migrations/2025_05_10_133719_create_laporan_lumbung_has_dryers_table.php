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
        Schema::create('laporan_lumbung_has_dryers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_lumbung_id')->constrained()->onDelete('cascade');
            $table->foreignId('dryer_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_lumbung_has_dryers');
    }
};
