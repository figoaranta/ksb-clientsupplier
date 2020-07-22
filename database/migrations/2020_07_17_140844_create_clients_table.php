<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nomorBon');
            $table->string('pembeli');
            $table->string('penerima');
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
        Schema::dropIfExists('clients');
    }
}
