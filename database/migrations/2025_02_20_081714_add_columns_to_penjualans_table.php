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
        Schema::table('penjualans', function (Blueprint $table) {
            $table->string('nama_barang');

            $table->string('brondolan')->nullable();
            $table->integer('bruto');
            $table->integer('tara')->nullable();
            $table->integer('netto')->nullable();
            $table->string('kepemilikan'); // Kolom untuk kepemilikan
            $table->time('jam_masuk');
            $table->time('jam_keluar')->nullable();
            $table->string('no_lumbung');
            $table->string('nama_lumbung');
            $table->timestamps();
            
             // Foreign key ke tabel suppliers
    $table->foreignId('id_supplier')->constrained('suppliers')->onDelete('cascade');
    
    // Foreign key ke tabel mobils
    $table->foreignId('id_mobil')->constrained('mobils')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            //
        });
    }
};
