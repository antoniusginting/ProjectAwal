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
        Schema::table('dryers', function (Blueprint $table) {

            // Foreign key ke tabel kapasitas dryer
            $table->unsignedBigInteger('id_kapasitas_dryer');
            $table->foreign('id_kapasitas_dryer')->references('id')->on('kapasitas_dryers')->onDelete('cascade');

            //Foreign key ke lumbung 1
            $table->unsignedBigInteger('id_lumbung_1');
            $table->foreign('id_lumbung_1')->references('id')->on('lumbung_basahs')->onDelete('cascade');

            //Foreign key ke lumbung 2
            $table->unsignedBigInteger('id_lumbung_2');
            $table->foreign('id_lumbung_2')->references('id')->on('lumbung_basahs')->onDelete('cascade');

            $table->string('operator');
            $table->string('jenis_jagung');
            $table->string('lumbung_tujuan');
            $table->float('rencana_kadar');
            $table->float('hasil_kadar');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dryers', function (Blueprint $table) {
            //
        });
    }
};
