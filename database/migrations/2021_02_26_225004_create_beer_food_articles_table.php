<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeerFoodArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beer_food_articles', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->text('text');
            $table->string('video_link')->nullable();
            $table->foreignId('beer_food_category_id')->constrained();

            $table->foreignId('create_user_id')->constrained('users');
            $table->foreignId('update_user_id')->constrained('users');
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
        Schema::dropIfExists('beer_food_articles');
    }
}
