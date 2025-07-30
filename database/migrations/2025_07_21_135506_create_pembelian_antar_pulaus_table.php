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
        if (!Schema::hasTable('pembelian_antar_pulaus')) {
            Schema::create('pembelian_antar_pulaus', function (Blueprint $table) {
                $table->id(); // Changed from int(11) to bigIncrements
                $table->char('jenis', 2)->default('CB');
                $table->string('kode', 10)->unique()->nullable();
                $table->string('nama_barang');
                $table->string('no_container')->nullable();
                $table->integer('netto');
                $table->string('nama_ekspedisi')->nullable();
                $table->string('kode_segel')->nullable();
                $table->timestamps();
                $table->foreignId('kapasitas_kontrak_beli_id')->nullable()->constrained('kapasitas_kontrak_belis')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian_antar_pulaus');
    }
};
