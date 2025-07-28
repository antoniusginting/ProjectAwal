<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan_antar_pulaus', function (Blueprint $table) {
            if (!Schema::hasColumn('penjualan_antar_pulaus', 'pembelian_antar_pulau_id')) {
                $table->foreignId('pembelian_antar_pulau_id')
                    ->nullable()
                    ->after('no_container')
                    ->constrained('pembelian_antar_pulaus')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('penjualan_antar_pulaus', function (Blueprint $table) {
            $table->dropForeign(['pembelian_antar_pulau_id']);
            $table->dropColumn('pembelian_antar_pulau_id');
        });
    }
};
