<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('models', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('type');
            $table->foreign('type')->references('id')->on('algorithms')->onDelete('cascade');

            $table->dateTime('date');

            $table->integer('report_num_rows');
            $table->decimal('report_precision_0');
            $table->decimal('report_precision_1');
            $table->decimal('report_recall_0');
            $table->decimal('report_recall_1');
            $table->decimal('report_f1_score_0');
            $table->decimal('report_f1_score_1');
            $table->decimal('report_accuracy_precision');
            $table->decimal('report_accuracy_recall');
            $table->decimal('report_accuracy_f1_score');

            //seleccion de columnas
            $table->boolean('attribute_date');
            $table->boolean('attribute_time');
            $table->boolean('attribute_id');
            $table->boolean('attribute_airline');
            $table->boolean('attribute_destination');
            $table->boolean('attribute_temperature');
            $table->boolean('attribute_humidity');
            $table->boolean('attribute_wind_speed');
            $table->boolean('attribute_wind_direction');
            $table->boolean('attribute_pressure');
            $table->boolean('attribute_airport_id');

            $table->string('airports');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('models');
    }
}
