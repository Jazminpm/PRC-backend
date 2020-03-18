<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeathersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weathers', function (Blueprint $table) {//wind_directions-id, direction
            $table->dateTime('dateTime');
            $table->integer('temperature');
            $table->integer('humidity');
            $table->integer('pressure');
            $table->integer('wind_direction')->unsigned();
            $table->foreign('wind_direction')->references('id')->on('wind_directions');
            $table->integer('wind');
            $table->integer('airport_id')->unsigned();
            $table->foreign('airport_id')->references('id')->on('airports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('weathers');
    }
}
