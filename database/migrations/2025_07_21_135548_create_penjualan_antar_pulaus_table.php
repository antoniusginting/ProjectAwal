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
        Schema::create('penjualan_antar_pulaus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('jenis', 2)->default('CJ');
            $table->string('kode', 10)->unique()->nullable();
            $table->string('nama_barang');
            $table->string('no_container')->nullable();
            $table->integer('netto');
            $table->integer('netto_diterima')->nullable();
            $table->string('nama_ekspedisi')->nullable();
            $table->string('kode_segel')->nullable();
            $table->timestamps();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('kapasitas_kontrak_jual_id')->nullable()->constrained('kapasitas_kontrak_juals')->onDelete('cascade');
            $table->string('status', 25)->nullable();
            $table->decimal('jumlah_setengah', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_antar_pulaus');
    }
};
