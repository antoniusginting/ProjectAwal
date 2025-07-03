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
        Schema::table('transfers', function (Blueprint $table) {
            // Foreign key untuk lumbung keluar (asal)
            $table->unsignedBigInteger('laporan_lumbung_keluar_id')->nullable();
            $table->foreign('laporan_lumbung_keluar_id')->references('id')->on('laporan_lumbungs')->onDelete('cascade');
            
            // Foreign key untuk lumbung masuk (tujuan)
            $table->unsignedBigInteger('laporan_lumbung_masuk_id')->nullable();
            $table->foreign('laporan_lumbung_masuk_id')->references('id')->on('laporan_lumbungs')->onDelete('cascade');
            
            // Status transfer
            // $table->enum('status', ['masuk', 'keluar'])->default('keluar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfers', function (Blueprint $table) {
            // Drop foreign key constraint terlebih dahulu
            $table->dropForeign(['laporan_lumbung_id']);

            // Kemudian drop kolom
            $table->dropColumn('laporan_lumbung_id');
        });
    }
};
