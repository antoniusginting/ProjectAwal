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
        Schema::create('stock_luars', function (Blueprint $table) {
            $table->id();

            $table->foreignId('silo_id')->constrained()->onDelete('cascade');
            $table->integer('quantity'); // Jumlah stok yang ditambahkan
            $table->text('notes')->nullable(); // Catatan
            $table->date('date_added'); // Tanggal penambahan

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_luars');
    }
};
