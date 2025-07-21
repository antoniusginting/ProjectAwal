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
            $table->bigIncrements('id');
            $table->foreignId('dryer_id')->constrained('dryers')->onDelete('cascade');
            $table->foreignId('sortiran_id')->constrained('sortirans')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dryer_has_sortiran');
    }
};
