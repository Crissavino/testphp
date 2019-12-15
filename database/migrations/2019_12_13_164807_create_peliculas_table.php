<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeliculasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('peliculas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('idMovieDB');
            $table->string('title');
            $table->integer('posicion_toprated');
            $table->float('popularity');
            $table->integer('vote_count');
            $table->boolean('video');
            $table->string('poster_path');
            $table->boolean('adult');
            $table->string('backdrop_path');
            $table->string('original_language');
            $table->string('original_title');
            $table->float('vote_average');
            $table->longText('overview');
            $table->string('release_date');
            $table->json('cast')->nullable();
            $table->softDeletesTz();
            $table->timestampsTz();
        });

        // Schema::create('genres', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('name');
        //     $table->softDeletesTz();
        //     $table->timestampsTz();
        // });

        Schema::create('pelicula_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('pelicula_id')->unsigned();
            $table->integer('user_id')->unsigned();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('peliculas');
        Schema::dropIfExists('pelicula_user');
    }
}
