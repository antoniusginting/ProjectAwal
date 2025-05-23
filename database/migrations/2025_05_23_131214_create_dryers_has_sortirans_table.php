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
        Schema::create('dryer_has_sortiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dryer_id')->constrained()->onDelete('cascade');
            $table->foreignId('sortiran_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dryers_has_sortirans');
    }
};
