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
        Schema::create('timbangan_trontons', function (Blueprint $table) {
            $table->id();

            //Foreign key ke laporan penjualan 1
            $table->unsignedBigInteger('id_timbangan_jual_1');
            $table->foreign('id_timbangan_jual_1')->references('id')->on('penjualans')->onDelete('cascade');
            
            //Foreign key ke laporan penjualan 2
            $table->unsignedBigInteger('id_timbangan_jual_2');
            $table->foreign('id_timbangan_jual_2')->references('id')->on('penjualans')->onDelete('cascade');

            //Foreign key ke laporan penjualan 3
            $table->unsignedBigInteger('id_timbangan_jual_3');
            $table->foreign('id_timbangan_jual_3')->references('id')->on('penjualans')->onDelete('cascade');

            //Foreign key ke laporan penjualan 4
            $table->unsignedBigInteger('id_timbangan_jual_4');
            $table->foreign('id_timbangan_jual_4')->references('id')->on('penjualans')->onDelete('cascade');
            
            //Foreign key ke laporan penjualan 5
            $table->unsignedBigInteger('id_timbangan_jual_5');
            $table->foreign('id_timbangan_jual_5')->references('id')->on('penjualans')->onDelete('cascade');

            //Foreign key ke laporan penjualan 6
            $table->unsignedBigInteger('id_timbangan_jual_6');
            $table->foreign('id_timbangan_jual_6')->references('id')->on('penjualans')->onDelete('cascade');

            $table->integer('total_bruto');
            $table->integer('tambah_berat');
            $table->integer('berat_final');
            $table->string('keterangan');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timbangan_tronton');
    }
};
