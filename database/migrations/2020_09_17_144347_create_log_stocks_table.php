<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('LogStoks', function (Blueprint $table) {
            $table->id();
            $table->integer("wheelId");
            $table->string("uniqueCode")->nullable();
            $table->string("name");
            $table->integer("quantity");
            $table->integer("price");
            $table->string("date");
            $table->string("keterangan");
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
        Schema::dropIfExists('LogStoks');
    }
}
