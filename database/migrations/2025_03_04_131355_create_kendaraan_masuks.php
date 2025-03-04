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
        Schema::create('kendaraan_masuks', function (Blueprint $table) {
            $table->id();

            $table->string('status');
            $table->string('nama_sup_per');
            $table->string('plat_polisi')->nullable();
            $table->string('nama_barang')->nullable();
            $table->string('keterangan')->nullable();
            $table->time('jam_masuk');
            $table->time('jam_keluar')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kendaraan_masuks');
    }
};
