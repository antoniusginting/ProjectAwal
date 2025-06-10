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
        Schema::create('silos_has_laporan_lumbungs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('silo_id')->constrained()->onDelete('cascade');
            $table->foreignId('laporan_lumbung_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('silos_has_laporan_lumbungs');
    }
};
