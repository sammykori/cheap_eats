<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id('menu_id');
            $table->string('food_name');
            $table->string('food_type');
            $table->string('food_price');
            $table->string('short_description')->nullable();
            $table->string('long_description')->nullable();
            $table->string('food_image_path')->nullable();
            $table->enum('menu_status', ['AVAILABLE', 'UNAVAILABLE'])->default('AVAILABLE');
            $table->enum('delete_status', ['DELETED','NOT DELETED'])->default('NOT DELETED');
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
        Schema::dropIfExists('menus');
    }
}
