<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentMaintenanceTutorialStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_maintenance_tutorial_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('equipment_maintenance_tutorial_sections');
            $table->text('name');
            $table->text('description');
            $table->string('video_link')->nullable();

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
        Schema::dropIfExists('equipment_maintenance_tutorial_steps');
    }
}
