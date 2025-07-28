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
        Schema::table('penjualan_antar_pulaus', function (Blueprint $table) {
            if (!Schema::hasColumn('penjualan_antar_pulaus', 'pembelian_antar_pulau_id')) {
                $table->unsignedBigInteger('pembelian_antar_pulau_id')->nullable()->after('id');

                // Jika tabel `pembelian_antar_pulaus` ada, tambahkan foreign key
                if (Schema::hasTable('pembelian_antar_pulaus')) {
                    $table->foreign('pembelian_antar_pulau_id')
                        ->references('id')
                        ->on('pembelian_antar_pulaus')
                        ->nullOnDelete();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualan_antar_pulaus', function (Blueprint $table) {
            if (Schema::hasColumn('penjualan_antar_pulaus', 'pembelian_antar_pulau_id')) {
                $table->dropForeign(['pembelian_antar_pulau_id']);
                $table->dropColumn('pembelian_antar_pulau_id');
            }
        });
    }
};
