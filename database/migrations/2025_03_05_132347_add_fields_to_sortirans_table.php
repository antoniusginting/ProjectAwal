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
        Schema::table('sortirans', function (Blueprint $table) {
            
            // // Foreign key ke tabel Kapasitas Lumbung Basah
            // $table->unsignedBigInteger('id_lumbung_basah');
            // $table->foreign('id_lumbung_basah')->references('id')->on('kapasitas_lumbung_basahs')->onDelete('cascade');
            // Foreign key ke tabel pembelians
            $table->unsignedBigInteger('id_pembelian');
            $table->foreign('id_pembelian')->references('id')->on('pembelians')->onDelete('cascade');

            $table->integer('no_lumbung');
            $table->integer('total_karung');
            // Data kualitas jagung 1
            $table->string('kualitas_jagung_1');
            $table->string('foto_jagung_1')->nullable();
            $table->string('x1_x10_1');
            $table->integer('jumlah_karung_1');
            $table->integer('tonase_1');

            // Data kualitas jagung 2
            $table->string('kualitas_jagung_2')->nullable();
            $table->string('foto_jagung_2')->nullable();
            $table->string('x1_x10_2')->nullable();
            $table->integer('jumlah_karung_2')->nullable();
            $table->integer('tonase_2')->nullable();

            // Data kualitas jagung 3
            $table->string('kualitas_jagung_3')->nullable();
            $table->string('foto_jagung_3')->nullable();
            $table->string('x1_x10_3')->nullable();
            $table->integer('jumlah_karung_3')->nullable();
            $table->integer('tonase_3')->nullable();

            // Data kualitas jagung 4
            $table->string('kualitas_jagung_4')->nullable();
            $table->string('foto_jagung_4')->nullable();
            $table->string('x1_x10_4')->nullable();
            $table->integer('jumlah_karung_4')->nullable();
            $table->integer('tonase_4')->nullable();

            // Data kualitas jagung 5
            $table->string('kualitas_jagung_5')->nullable();
            $table->string('foto_jagung_5')->nullable();
            $table->string('x1_x10_5')->nullable();
            $table->integer('jumlah_karung_5')->nullable();
            $table->integer('tonase_5')->nullable();

            // Data kualitas jagung 6
            $table->string('kualitas_jagung_6')->nullable();
            $table->string('foto_jagung_6')->nullable();
            $table->string('x1_x10_6')->nullable();
            $table->integer('jumlah_karung_6')->nullable();
            $table->integer('tonase_6')->nullable();

            $table->float('kadar_air');

            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sortirans', function (Blueprint $table) {
            //
        });
    }
};
