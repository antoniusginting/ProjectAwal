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
        if (!Schema::hasTable('kapasitas_dryers')) {
            Schema::create('kapasitas_dryers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('nama_kapasitas_dryer');
                $table->integer('kapasitas_total');
                $table->integer('kapasitas_sisa')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kapasitas_dryers');
    }
};
