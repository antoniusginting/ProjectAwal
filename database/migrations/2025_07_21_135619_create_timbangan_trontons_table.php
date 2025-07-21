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
        Schema::create('timbangan_trontons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('jenis', 1)->default('P');
            $table->string('kode', 10);
            $table->foreignId('id_timbangan_jual_1')->nullable()->constrained('penjualans')->onDelete('cascade');
            $table->foreignId('id_timbangan_jual_2')->nullable()->constrained('penjualans')->onDelete('cascade');
            $table->foreignId('id_timbangan_jual_3')->nullable()->constrained('penjualans')->onDelete('cascade');
            $table->foreignId('id_timbangan_jual_4')->nullable()->constrained('penjualans')->onDelete('cascade');
            $table->foreignId('id_timbangan_jual_5')->nullable()->constrained('penjualans')->onDelete('cascade');
            $table->foreignId('id_timbangan_jual_6')->nullable()->constrained('penjualans')->onDelete('cascade');
            $table->integer('bruto_akhir')->nullable();
            $table->integer('total_netto')->nullable();
            $table->integer('tara_awal')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('status', 25)->nullable();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->longText('foto')->nullable();
            $table->foreignId('id_penjualan_antar_pulau_1')->nullable()->constrained('penjualan_antar_pulaus')->onDelete('cascade');
            $table->foreignId('id_penjualan_antar_pulau_2')->nullable()->constrained('penjualan_antar_pulaus')->onDelete('cascade');
            $table->foreignId('id_penjualan_antar_pulau_3')->nullable()->constrained('penjualan_antar_pulaus')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timbangan_trontons');
    }
};
