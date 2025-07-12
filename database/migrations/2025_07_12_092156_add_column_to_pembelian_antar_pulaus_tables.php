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
        Schema::table('pembelian_antar_pulaus', function (Blueprint $table) {
            $table->string('nama_barang');
            $table->string('no_container')->nullable();
            $table->integer('netto');
            $table->string('nama_ekspedisi')->nullable();
            $table->string('kode_segel')->nullable();
            $table->timestamps();

            //Foreign key ke laporan lumbung
            $table->unsignedBigInteger('luar_pulau_id')->nullable();
            $table->foreign('luar_pulau_id')->references('id')->on('luar_pulaus')->onDelete('cascade');
            //Foreign key ke laporan lumbung
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('=pembelian_antar_pulaus', function (Blueprint $table) {
            //
        });
    }
};
