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
            $table->unsignedBigInteger('orders_order_id');
            $table->string('receiver_name');
            $table->string('receiver_location');
            $table->enum('delete_status', ['DELETED', 'NOT DELETED'])->default('NOT DELETED');
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
        Schema::dropIfExists('order_for_non_customers');
    }
}
