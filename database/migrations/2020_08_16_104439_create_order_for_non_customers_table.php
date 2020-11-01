<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderForNonCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_for_non_customers', function (Blueprint $table) {
            $table->id('order_for_non_customer_id');
            $table->unsignedBigInteger('orders_order_id')->nullable();
            $table->string('receiver_name');
            $table->string('receiver_location');
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
        Schema::dropIfExists('order_for_non_customers');
//        Schema::table('order_for_non_customers', function (Blueprint $table) {
//            $table->dropSoftDeletes();
//        });
    }
}
