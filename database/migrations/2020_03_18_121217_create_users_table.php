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
            $table->string('email')->unique();
            $table->string('name');
            $table->string('surnames')->nullable();
            $table->string('password');
            $table->string('dni')->unique()->nullable();
            $table->string('phoneNumber')->nullable();
            $table->string('remember_token')->nullable();
            $table->string('refresh_token')->nullable();
            $table->integer('role')->unsigned()->default(2);
            $table->foreign('role')->references('id')->on('roles');
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
