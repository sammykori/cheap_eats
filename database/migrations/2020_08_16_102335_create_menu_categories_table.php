<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id('menu_category_id');
            $table->string('category_name');
            $table->string('category_description');
            $table->enum('category_status', ['AVAILABLE', 'NOT AVAILABLE'])->default('AVAILABLE');
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
        Schema::dropIfExists('menu_categories');
//        Schema::table('menu_categories', function (Blueprint $table) {
//            $table->dropSoftDeletes();
//        });
    }
}
