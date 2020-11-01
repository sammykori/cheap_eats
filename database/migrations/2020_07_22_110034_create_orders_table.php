<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->uuid('uuid');
            $table->unsignedBigInteger('customers_customer_id')->nullable();
            $table->unsignedBigInteger('menu_id');
            $table->string('quantity');
            $table->string('food_priced_amount')->nullable();
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
        Schema::dropIfExists('orders');
//        Schema::table('orders', function (Blueprint $table) {
//            $table->dropSoftDeletes();
//        });
    }
}
