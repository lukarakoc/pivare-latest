<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeerQasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beer_qas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beer_qa_category_id')->constrained('beer_qa_categories');
            $table->text('question');
            $table->text('answer');

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
        Schema::dropIfExists('beer_qas');
    }
}
