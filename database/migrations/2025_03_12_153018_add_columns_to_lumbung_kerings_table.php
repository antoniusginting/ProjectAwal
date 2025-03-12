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
            $table->string('jenis_jagung');

            // Foreign key ke tabel kapasitas dryer
            $table->unsignedBigInteger('id_kapasitas_lumbung_kering');
            $table->foreign('id_kapasitas_lumbung_kering')->references('id')->on('kapasitas_lumbung_kerings')->onDelete('cascade');

            // Foreign key ke tabel kapasitas dryer
            $table->unsignedBigInteger('id_dryer');
            $table->foreign('id_dryer')->references('id')->on('dryers')->onDelete('cascade');
            $table->timestamps();
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
