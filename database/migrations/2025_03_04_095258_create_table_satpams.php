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
        Schema::create('gerbangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_supir');
            $table->string('plat_polisi');
            $table->time('jam_masuk');
            $table->string('nama_barang')->nullable();
            $table->integer('keterangan');
            $table->time('jam_keluar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_satpams');
    }
};
