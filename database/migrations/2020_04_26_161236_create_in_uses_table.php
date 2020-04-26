<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInUsesTable extends Migration
{
    /**
     * Run the migrations.
     *comments
     * @return void
     */
    public function up()
    {
        Schema::create('in_uses', function (Blueprint $table) {
            $table->integer('model')->unsigned();
            $table->foreign('model')->references('id')->on('models')->onDelete('cascade');

            $table->integer('analysis');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('in_uses');
    }
}
