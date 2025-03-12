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
        Schema::table('lumbung_kerings', function (Blueprint $table) {
            //Foreign key ke laporan penjualan 1
            $table->unsignedBigInteger('id_laporan_penjualan_1');
            $table->foreign('id_laporan_penjualan_1')->references('id')->on('laporan_penjualans')->onDelete('cascade');
            $table->integer('berat_1');
            
            //Foreign key ke laporan penjualan 2
            $table->unsignedBigInteger('id_laporan_penjualan_2');
            $table->foreign('id_laporan_penjualan_2')->references('id')->on('laporan_penjualans')->onDelete('cascade');
            $table->integer('berat_2');

            //Foreign key ke laporan penjualan 3
            $table->unsignedBigInteger('id_laporan_penjualan_3');
            $table->foreign('id_laporan_penjualan_3')->references('id')->on('laporan_penjualans')->onDelete('cascade');
            $table->integer('berat_3');

            //Foreign key ke laporan penjualan 4
            $table->unsignedBigInteger('id_laporan_penjualan_4');
            $table->foreign('id_laporan_penjualan_4')->references('id')->on('laporan_penjualans')->onDelete('cascade');
            $table->integer('berat_4');
            
            //Foreign key ke laporan penjualan 5
            $table->unsignedBigInteger('id_laporan_penjualan_5');
            $table->foreign('id_laporan_penjualan_5')->references('id')->on('laporan_penjualans')->onDelete('cascade');
            $table->integer('berat_5');

            //Foreign key ke laporan penjualan 6
            $table->unsignedBigInteger('id_laporan_penjualan_6');
            $table->foreign('id_laporan_penjualan_6')->references('id')->on('laporan_penjualans')->onDelete('cascade');
            $table->integer('berat_6');
            
            //Foreign key ke laporan penjualan 7
            $table->unsignedBigInteger('id_laporan_penjualan_7');
            $table->foreign('id_laporan_penjualan_7')->references('id')->on('laporan_penjualans')->onDelete('cascade');
            $table->integer('berat_7');

            //Foreign key ke laporan penjualan 8
            $table->unsignedBigInteger('id_laporan_penjualan_8');
            $table->foreign('id_laporan_penjualan_8')->references('id')->on('laporan_penjualans')->onDelete('cascade');
            $table->integer('berat_8');

            //Foreign key ke laporan penjualan 9
            $table->unsignedBigInteger('id_laporan_penjualan_9');
            $table->foreign('id_laporan_penjualan_9')->references('id')->on('laporan_penjualans')->onDelete('cascade');
            $table->integer('berat_9');
            
            //Foreign key ke laporan penjualan 10
            $table->unsignedBigInteger('id_laporan_penjualan_10');
            $table->foreign('id_laporan_penjualan_10')->references('id')->on('laporan_penjualans')->onDelete('cascade');
            $table->integer('berat_10');

            $table->integer('total_berat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lumbung_kerings', function (Blueprint $table) {
            //
        });
    }
};
