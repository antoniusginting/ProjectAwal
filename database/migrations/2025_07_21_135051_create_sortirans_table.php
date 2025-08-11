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
        if (!Schema::hasTable('sortirans')) {
            Schema::create('sortirans', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->char('jenis', 1)->default('S');
                $table->string('no_sortiran', 10)->unique()->nullable();
                $table->foreignId('id_pembelian')->nullable()->constrained('pembelians')->onDelete('cascade');
                $table->integer('total_karung')->nullable();
                $table->integer('berat_tungkul')->nullable();
                $table->string('netto_bersih', 25)->nullable();
                $table->string('kualitas_jagung_1')->nullable();
                $table->string('x1_x10_1')->nullable();
                $table->string('jumlah_karung_1', 11)->nullable();
                $table->string('tonase_1', 25)->nullable();
                $table->string('kualitas_jagung_2')->nullable();
                $table->string('x1_x10_2')->nullable();
                $table->string('jumlah_karung_2', 11)->nullable();
                $table->string('tonase_2', 25)->nullable();
                $table->string('kualitas_jagung_3')->nullable();
                $table->string('x1_x10_3')->nullable();
                $table->string('jumlah_karung_3', 11)->nullable();
                $table->string('tonase_3', 25)->nullable();
                $table->string('kualitas_jagung_4')->nullable();
                $table->string('x1_x10_4')->nullable();
                $table->string('jumlah_karung_4', 11)->nullable();
                $table->string('tonase_4', 25)->nullable();
                $table->string('kualitas_jagung_5')->nullable();
                $table->string('x1_x10_5')->nullable();
                $table->string('jumlah_karung_5', 11)->nullable();
                $table->string('tonase_5', 25)->nullable();
                $table->string('kualitas_jagung_6')->nullable();
                $table->string('x1_x10_6')->nullable();
                $table->string('jumlah_karung_6', 11)->nullable();
                $table->string('tonase_6', 25)->nullable();
                $table->longText('foto_jagung_1')->nullable();
                $table->double('kadar_air')->nullable();
                $table->timestamps();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('keterangan', 50)->nullable();
                $table->boolean('verif')->nullable();
                $table->integer('cek')->nullable();
                $table->enum('status', ['available', 'in_dryer', 'completed'])->default('available');
                $table->foreignId('no_lumbung_basah')->nullable()->constrained('kapasitas_lumbung_basahs')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sortirans');
    }
};
