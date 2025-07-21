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
        Schema::create('pembelians', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('jenis', 1)->default('B');
            $table->string('nama_supir', 100)->nullable();
            $table->string('no_spb', 10)->unique()->nullable();
            $table->string('nama_barang');
            $table->string('no_container')->nullable();
            $table->string('brondolan')->nullable();
            $table->string('plat_polisi')->nullable();
            $table->integer('bruto');
            $table->integer('tara')->nullable();
            $table->integer('netto')->nullable();
            $table->string('keterangan')->nullable();
            $table->time('jam_masuk');
            $table->time('jam_keluar')->nullable();
            $table->timestamps();
            $table->foreignId('id_supplier')->nullable()->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('jumlah_karung')->nullable();
            $table->longText('foto')->nullable(); // For longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelians');
    }
};
