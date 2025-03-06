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
        Schema::table('lumbung_basahs', function (Blueprint $table) {

            //Foreign key ke kapasitas lumbung basah
            $table->unsignedBigInteger('no_lumbung_basah');
            $table->foreign('no_lumbung_basah')->references('id')->on('kapasitas_lumbung_basahs')->onDelete('cascade');
            
            //Foreign key ke sortiran
            $table->unsignedBigInteger('id_sortiran_1');
            $table->foreign('id_sortiran_1')->references('id')->on('sortirans')->onDelete('cascade');
            
            //Foreign key ke sortiran
            $table->unsignedBigInteger('id_sortiran_2');
            $table->foreign('id_sortiran_2')->references('id')->on('sortirans')->onDelete('cascade');
            
            //Foreign key ke sortiran
            $table->unsignedBigInteger('id_sortiran_3');
            $table->foreign('id_sortiran_3')->references('id')->on('sortirans')->onDelete('cascade');
            $table->integer('total_netto');
            //Foreign key ke sortiran
            $table->unsignedBigInteger('id_sortiran_4');
            $table->foreign('id_sortiran_4')->references('id')->on('sortirans')->onDelete('cascade');
            //Foreign key ke sortiran
            $table->unsignedBigInteger('id_sortiran_5');
            $table->foreign('id_sortiran_5')->references('id')->on('sortirans')->onDelete('cascade');
            
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lumbung_basahs', function (Blueprint $table) {
            //
        });
    }
};
