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
        Schema::create('sortirans_has_penjualans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sortiran_id')->constrained()->onDelete('cascade');
            $table->foreignId('penjualan_id')->constrained()->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sortirans_has_penjualans');
    }
};
