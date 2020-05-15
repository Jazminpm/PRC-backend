<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->string('sentiment')->nullable();
            $table->decimal('polarity', 8, 2)->nullable();
            $table->decimal('grade', 8, 2)->default(0.0);
            $table->string('title')->nullable();
            $table->string('place')->nullable();
            $table->text('original_message');
            $table->text('message'); # primary
            $table->string('library')->nullable();
            $table->dateTime('date_time'); # primary
            $table->integer('city_id')->unsigned();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
