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
        Schema::create('laporan_lumbungs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('jenis', 2)->default('IO');
            $table->string('kode', 10)->unique()->nullable();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('lumbung', 20)->nullable();
            $table->string('status_silo', 25)->nullable();
            $table->boolean('status')->nullable();
            $table->foreignId('silo_id')->nullable()->constrained('silos')->onDelete('cascade');
            $table->string('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_lumbungs');
    }
};
