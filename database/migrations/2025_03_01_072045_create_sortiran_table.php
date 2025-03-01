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
        Schema::create('sortiran', function (Blueprint $table) {
            $table->id(); // Auto increment primary key

            // Foreign key ke tabel pembelians
            $table->unsignedBigInteger('pembelian_id');
            $table->foreign('pembelian_id')->references('id')->on('pembelians')->onDelete('cascade');

            $table->string('lumbung');

            // Data kualitas jagung 1
            $table->string('kualitas_jagung_1');
            $table->string('foto_jagung_1')->nullable();
            $table->string('x1_x10_1');
            $table->integer('jumlah_karung_1');
            $table->float('kadar_air_1');

            // Data kualitas jagung 2
            $table->string('kualitas_jagung_2')->nullable();
            $table->string('foto_jagung_2')->nullable();
            $table->string('x1_x10_2')->nullable();
            $table->integer('jumlah_karung_2')->nullable();
            $table->float('kadar_air_2')->nullable();

            // Data kualitas jagung 3
            $table->string('kualitas_jagung_3')->nullable();
            $table->string('foto_jagung_3')->nullable();
            $table->string('x1_x10_3')->nullable();
            $table->integer('jumlah_karung_3')->nullable();
            $table->float('kadar_air_3')->nullable();

            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sortiran');
    }
};
