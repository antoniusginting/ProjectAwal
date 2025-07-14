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
        Schema::table('timbangan_trontons', function (Blueprint $table) {
             //Foreign key ke antar pulau 1
            $table->unsignedBigInteger('id_penjualan_antar_pulau_1')->nullable();
            $table->foreign('id_penjualan_antar_pulau_1')->references('id')->on('penjualan_antar_pulaus')->onDelete('cascade');
            
            //Foreign key ke antar pulau 2
            $table->unsignedBigInteger('id_penjualan_antar_pulau_2')->nullable();
            $table->foreign('id_penjualan_antar_pulau_2')->references('id')->on('penjualan_antar_pulaus')->onDelete('cascade');

            //Foreign key ke antar pulau 3
            $table->unsignedBigInteger('id_penjualan_antar_pulau_3')->nullable();
            $table->foreign('id_penjualan_antar_pulau_3')->references('id')->on('penjualan_antar_pulaus')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timbangan_trontons', function (Blueprint $table) {
            //
        });
    }
};
