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
        Schema::create('kapasitas_lumbung_basahs', function (Blueprint $table) {
            $table->id();

            $table->integer('no_kapasitas_lumbung');
            $table->integer('kapasitas_total');
            $table->integer('kapasitas_sisa');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kapasitas_lumbung_basah');
    }
};
