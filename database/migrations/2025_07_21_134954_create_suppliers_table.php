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
            $table->bigIncrements('id');
            $table->string('nama_supplier')->unique();
            $table->string('jenis_supplier')->nullable();
            $table->string('no_ktp', 30)->nullable();
            $table->string('npwp', 30)->nullable();
            $table->string('no_rek', 30)->nullable();
            $table->string('nama_bank', 50)->nullable();
            $table->string('atas_nama_bank', 50)->nullable();
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
