<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id('order_delivery_id');
            $table->unsignedBigInteger('orders_order_id');
            $table->string('delivery_token');
            $table->enum('delivery_type', ['SELF','SOMEONE'])->default('SELF');
            $table->string('delivery_location');
            $table->time("departure_time")->nullable();
            $table->time("delivery_time")->nullable();
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
        Schema::dropIfExists('order_deliveries');
//        Schema::table('order_deliveries', function (Blueprint $table) {
//            $table->dropSoftDeletes();
//        });
    }
}
