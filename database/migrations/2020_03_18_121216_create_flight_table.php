<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlightTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flight', function (Blueprint $table) {
            $table->string('id');
            $table->dateTime('date');
            $table->primary(['id', 'date']);
            $table->integer('airline_id')->unsigned();
            $table->foreign('airline_id')
                ->references('id')->on('airline');
            $table->string('destination');
            $table->integer('delay');//retraso(mins)
            $table->dateTime('expected_departure_time');
            $table->integer('airport_id')->unsigned();
            $table->foreign('airport_id')
                ->references('id')->on('airport')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flight');
    }
}
