<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAirportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('airports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');

            $table->integer('city_id')->unsigned();
            $table->foreign('city_id')->references('id')->on('cities');

            $table->string('iata')->nullable();
            $table->string('icao')->nullable();
            $table->float('latitude', 8, 6);
            $table->float('longitude', 8,5);
            $table->integer('altitude');

            $table->string('timezone')->nullable();
            $table->string('dst')->nullable();
            $table->string('tz')->nullable();

            $table->string('airport_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('airports');
    }
}
