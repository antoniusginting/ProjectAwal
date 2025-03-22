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
        Schema::create('alamat_kontraks', function (Blueprint $table) {
            $table->id();

            //Foreign key ke laporan penjualan 6
            $table->unsignedBigInteger('id_kontrak');
            $table->foreign('id_kontrak')->references('id')->on('kontraks')->onDelete('cascade');
            $table->string('alamat');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alamat_kontraks');
    }
};
