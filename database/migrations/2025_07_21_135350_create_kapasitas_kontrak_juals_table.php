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
        if (!Schema::hasTable('kapasitas_kontrak_juals')) {
            Schema::create('kapasitas_kontrak_juals', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('nama', 50)->nullable();
                $table->integer('no_po')->nullable();
                $table->integer('stok')->nullable();
                $table->boolean('status');
                $table->integer('harga')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kapasitas_kontrak_juals');
    }
};
