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
            //Foreign key ke laporan lumbung
            $table->unsignedBigInteger('laporan_lumbung_id')->nullable();
            $table->foreign('laporan_lumbung_id')->references('id')->on('laporan_lumbungs')->onDelete('cascade');
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
