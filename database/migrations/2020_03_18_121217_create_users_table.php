<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('surname')->nullable();
            $table->string('password');
            $table->string('dni')->unique()->nullable();
            $table->string('phoneNumber')->nullable();
            $table->string('email')->unique();
            $table->integer('role')->unsigned()->nullable();
            $table->foreign('role')->references('id')->on('roles');
            $table->string('flight_id')->nullable()->nullable();
            $table->dateTime('date_time')->nullable();
            $table->foreign(array('flight_id', 'date_time'))->references(array('id', 'date_time'))->on('flights');
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
        Schema::dropIfExists('users');
    }
}
