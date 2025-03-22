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
        Schema::create('surat_jalans', function (Blueprint $table) {
            $table->id();

            //Foreign key ke kontrak 1 
            $table->unsignedBigInteger('id_kontrak');
            $table->foreign('id_kontrak')->references('id')->on('kontraks')->onDelete('cascade');
            //Foreign key ke kontrak 2
            $table->unsignedBigInteger('id_kontrak2');
            $table->foreign('id_kontrak2')->references('id')->on('kontraks')->onDelete('cascade');
            //Foreign key ke kontrak
            $table->unsignedBigInteger('id_alamat');
            $table->foreign('id_alamat')->references('id')->on('alamat_kontraks')->onDelete('cascade');
            //Foreign key ke kontrak
            $table->unsignedBigInteger('id_timbangan_tronton');
            $table->foreign('id_timbangan_tronton')->references('id')->on('timbangan_trontons')->onDelete('cascade');
            $table->string('kota');
            $table->string('po');
            $table->string('netto');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_jalans');
    }
};
