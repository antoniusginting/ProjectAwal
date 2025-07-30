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
        if (!Schema::hasTable('kapasitas_lumbung_basahs')) {
            Schema::create('kapasitas_lumbung_basahs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('no_kapasitas_lumbung', 25)->unique();
                $table->integer('kapasitas_total')->nullable();
                $table->integer('kapasitas_sisa')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kapasitas_lumbung_basahs');
    }
};
