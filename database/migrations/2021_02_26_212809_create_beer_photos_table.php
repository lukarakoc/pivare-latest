<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeerPhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beer_photos', function (Blueprint $table) {
            $table->id();
            $table->string('photo_path');
            $table->foreignId('beer_id')->constrained();

            $table->foreignId('create_user_id')->constrained('users');
            $table->foreignId('update_user_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('beer_photos');
    }
}
