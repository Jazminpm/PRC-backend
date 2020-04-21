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
            $table->dateTime('date_time');
            $table->primary(['id', 'date_time']);
            $table->integer('airline_id')->unsigned();
            $table->foreign('airline_id')->references('id')->on('airlines');
            $table->string('destination');
            $table->integer('delay'); // retraso(mins)
            $table->integer('airport_id')->unsigned();
            $table->foreign('airport_id')->references('id')->on('airports')->onDelete('cascade');
            $table->foreign('delay')->references('id')->on('flight_statuses')->onDelete('cascade');
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
