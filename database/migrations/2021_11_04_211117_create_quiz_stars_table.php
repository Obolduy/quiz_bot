<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizStarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quiz_stars', function (Blueprint $table) {
            $table->id();
            $table->integer('quiz_id');
            $table->integer('votes_count')->nullable();
            $table->integer('stars_avg')->nullable();
            $table->integer('stars_count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quiz_stars');
    }
}
