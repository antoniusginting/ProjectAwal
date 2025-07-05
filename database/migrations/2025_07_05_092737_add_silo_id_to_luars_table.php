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
        Schema::table('luars', function (Blueprint $table) {
            //Foreign key ke laporan lumbung
            $table->unsignedBigInteger('silo_id')->nullable();
            $table->foreign('silo_id')->references('id')->on('silos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('luars', function (Blueprint $table) {
            //
        });
    }
};
