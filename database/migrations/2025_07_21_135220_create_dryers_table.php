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
        if (!Schema::hasTable('dryers')) {
            Schema::create('dryers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->char('jenis', 1)->default('D');
                $table->string('no_dryer', 10)->unique()->nullable();
                $table->foreignId('id_kapasitas_dryer')->constrained('kapasitas_dryers')->onDelete('cascade');
                $table->string('operator')->nullable();
                $table->string('nama_barang')->nullable();
                $table->double('rencana_kadar')->nullable();
                $table->double('hasil_kadar')->nullable();
                $table->integer('total_netto')->nullable();
                $table->string('pj', 50)->nullable();
                $table->enum('status', ['processing', 'completed'])->default('processing');
                $table->timestamps();
                $table->string('no_cc', 50)->nullable();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('laporan_lumbung_id')->nullable()->constrained('laporan_lumbungs')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dryers');
    }
};
