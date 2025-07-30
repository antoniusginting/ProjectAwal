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
        if (!Schema::hasTable('kendaraans')) {
            Schema::create('kendaraans', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('plat_polisi_terbaru');
                $table->string('plat_polisi_sebelumnya')->nullable();
                $table->string('pemilik')->nullable();
                $table->string('nama_supir')->nullable();
                $table->string('nama_kernek')->nullable();
                $table->string('jenis_mobil')->nullable();
                $table->string('status_sp')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kendaraans');
    }
};
