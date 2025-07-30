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
        if (!Schema::hasTable('alamat_kontraks')) {
            Schema::create('alamat_kontraks', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('id_kontrak')->constrained('kontraks')->onDelete('cascade');
                $table->string('alamat');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alamat_kontraks');
    }
};
