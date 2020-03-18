<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->string('id');
            $table->dateTime('date');
            $table->primary(['id', 'date']);
            $table->integer('airline_id')->unsigned();
            $table->foreign('airline_id')->references('id')->on('airlines');
            $table->string('destination');
            $table->integer('delay'); // retraso(mins)
            $table->dateTime('expected_departure_time');
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
        Schema::dropIfExists('flights');
    }
}
