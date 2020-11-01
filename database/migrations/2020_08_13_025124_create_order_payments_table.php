<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id('order_payment_id');
            $table->unsignedBigInteger('orders_order_id');
            $table->string('payment_status');
            $table->enum('payment_type', ['CASH','MOMO']);
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
        Schema::dropIfExists('order_payments');
//        Schema::table('order_payments', function (Blueprint $table) {
//            $table->dropSoftDeletes();
//        });
    }
}
