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
        if (!Schema::hasTable('transfers')) {
            Schema::create('transfers', function (Blueprint $table) {
                $table->id(); // Changed from int(11) to bigIncrements
                $table->char('jenis', 1)->default('T');
                $table->string('nama_supir', 100)->nullable();
                $table->string('kode', 10)->unique()->nullable();
                $table->string('plat_polisi', 25)->nullable();
                $table->string('nama_barang');
                $table->integer('bruto')->nullable();
                $table->integer('tara')->nullable();
                $table->integer('netto')->nullable();
                $table->string('keterangan', 25)->nullable();
                $table->time('jam_masuk');
                $table->time('jam_keluar')->nullable();
                $table->timestamps();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('laporan_lumbung_keluar_id')->nullable()->constrained('laporan_lumbungs')->onDelete('cascade');
                $table->foreignId('laporan_lumbung_masuk_id')->nullable()->constrained('laporan_lumbungs')->onDelete('cascade');
                $table->foreignId('penjualan_id')->nullable()->constrained('penjualans')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
