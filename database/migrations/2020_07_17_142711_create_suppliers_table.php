<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('nomorBon');
            $table->string('penjual');
            $table->string('pembeli');
            $table->string('alamatPenerima')->nullable();
            $table->string('tanggalBayar')->nullable();
            $table->string('tanggalPengiriman')->nullable();
            $table->string('terbayar')->nullable();
            $table->string('order');
            $table->integer('hargaTotal');
            $table->integer('barangTotal');
            $table->string('keteranganBon')->nullable();
            $table->boolean('lunas')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suppliers');
    }
}
