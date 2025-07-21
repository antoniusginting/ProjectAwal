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
        Schema::create('surat_jalans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_kontrak')->constrained('kontraks')->onDelete('cascade');
            $table->foreignId('id_kontrak2')->nullable()->constrained('kontraks')->onDelete('cascade');
            $table->foreignId('id_alamat')->nullable()->constrained('alamat_kontraks')->onDelete('cascade');
            $table->foreignId('id_timbangan_tronton')->constrained('timbangan_trontons')->onDelete('cascade');
            $table->string('kota');
            $table->string('po')->nullable();
            $table->integer('tambah_berat')->nullable();
            $table->integer('bruto_final')->nullable();
            $table->integer('netto_final')->nullable();
            $table->string('jenis_mobil', 25)->nullable();
            $table->string('status', 25)->nullable();
            $table->integer('netto_diterima')->nullable();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kapasitas_kontrak_jual_id')->nullable()->constrained('kapasitas_kontrak_juals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_jalans');
    }
};
