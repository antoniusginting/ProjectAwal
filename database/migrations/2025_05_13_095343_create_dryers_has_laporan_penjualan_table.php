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
        Schema::create('dryers_has_timbangan_trontons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dryer_id')->constrained()->onDelete('cascade');
            $table->foreignId('timbangan_tronton_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dryers_has_timbangan_trontons');
    }
};
