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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->bigIncrements('id'); // Primary key, auto increment
            $table->string('nama_supplier')->unique(); // Kolom nama supplier
            $table->string('jenis_supplier'); // Kolom jenis supplier

            //Tambahan 15 Maret 2025
            $table->integer('no_ktp');
            $table->integer('npwp');
            $table->integer('no_rek');
            $table->string('nama_bank');
            $table->string('atas_nama_bank');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
