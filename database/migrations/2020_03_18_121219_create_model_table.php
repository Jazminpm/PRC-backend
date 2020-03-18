<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('model', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->dateTime('date');
            $table->integer('report_num_rows');
            $table->decimal('report_precision_0');
            $table->decimal('report_precision_1');
            $table->decimal('report_recall_0');
            $table->decimal('report_recall_1');
            $table->decimal('report_f1_score_0');
            $table->decimal('report_f1_score_1');
            $table->decimal('report_avg_0');
            $table->decimal('report_avg_1');

            //seleccion de columnas
            $table->boolean('attribute_date');
            $table->boolean('attribute_time');
            $table->boolean('attribute_id');
            $table->boolean('attribute_airline');
            $table->boolean('attribute_destination');
            $table->boolean('attribute_delay');
            $table->boolean('attribute_temperature');
            $table->boolean('attribute_humidity');
            $table->boolean('attribute_wind');
            $table->boolean('attribute_wind_direction');
            $table->boolean('attribute_pressure');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('model');
    }
}
