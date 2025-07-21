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
        Schema::create('penjualans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('jenis', 1)->default('J');
            $table->string('nama_supir', 100)->nullable();
            $table->string('no_spb', 10)->unique()->nullable();
            $table->string('nama_barang');
            $table->string('plat_polisi', 25)->nullable();
            $table->string('brondolan')->nullable();
            $table->integer('bruto')->nullable();
            $table->integer('tara')->nullable();
            $table->integer('netto')->nullable();
            $table->string('keterangan')->nullable();
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->string('nama_lumbung')->nullable();
            $table->timestamps();
            $table->string('no_container', 50)->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('jumlah_karung')->nullable();
            $table->foreignId('laporan_lumbung_id')->nullable()->constrained('laporan_lumbungs')->onDelete('cascade');
            $table->foreignId('silo_id')->nullable()->constrained('silos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
