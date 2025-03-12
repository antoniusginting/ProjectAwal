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
        Schema::table('laporan_penjualans', function (Blueprint $table) {
            $table->string('operator_timbangan');
            $table->string('jenis_jagung');
            $table->integer('jumlah_goni');

            //Foreign key ke lumbung kering 1
            $table->unsignedBigInteger('id_lumbung_kering_1');
            $table->foreign('id_lumbung_kering_1')->references('id')->on('lumbung_kerings')->onDelete('cascade');
            $table->integer('berat_1');
            
            //Foreign key ke lumbung kering 2
            $table->unsignedBigInteger('id_lumbung_kering_2');
            $table->foreign('id_lumbung_kering_2')->references('id')->on('lumbung_kerings')->onDelete('cascade');
            $table->integer('berat_2');

            //Foreign key ke lumbung kering 3
            $table->unsignedBigInteger('id_lumbung_kering_3');
            $table->foreign('id_lumbung_kering_3')->references('id')->on('lumbung_kerings')->onDelete('cascade');
            $table->integer('berat_3');

            //Foreign key ke lumbung kering 4
            $table->unsignedBigInteger('id_lumbung_kering_4');
            $table->foreign('id_lumbung_kering_4')->references('id')->on('lumbung_kerings')->onDelete('cascade');
            $table->integer('berat_4');
            
            //Foreign key ke lumbung kering 5
            $table->unsignedBigInteger('id_lumbung_kering_5');
            $table->foreign('id_lumbung_kering_5')->references('id')->on('lumbung_kerings')->onDelete('cascade');
            $table->integer('berat_5');

            //Foreign key ke lumbung kering 6
            $table->unsignedBigInteger('id_lumbung_kering_6');
            $table->foreign('id_lumbung_kering_6')->references('id')->on('lumbung_kerings')->onDelete('cascade');
            $table->integer('berat_6');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_penjualans', function (Blueprint $table) {
            //
        });
    }
};
