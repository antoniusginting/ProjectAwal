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
        if (!Schema::hasTable('kendaraan_masuks')) {
            Schema::create('kendaraan_masuks', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('status');
                $table->string('nama_sup_per');
                $table->string('plat_polisi')->nullable();
                $table->string('nama_barang')->nullable();
                $table->string('keterangan')->nullable();
                $table->time('jam_masuk')->nullable();
                $table->time('jam_keluar')->nullable();
                $table->timestamps();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->integer('nomor_antrian')->nullable();
                $table->integer('status_selesai')->nullable();
                $table->integer('status_awal')->nullable();
                $table->longText('foto')->nullable();
                $table->string('jenis', 20)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kendaraan_masuks');
    }
};
