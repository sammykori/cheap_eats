<?php

namespace App\Jobs;

use App\Order;
use App\OrderForNonCustomer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class OrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public  $order;
    public $session_id;
    public $uuid;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( $order, $session_id, $uuid)
    {
        $this->order = $order;
        $this->session_id = $session_id;
        $this->uuid = $uuid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order_details = $this->order;

        $order = Order::create([
            'uuid' => $this->uuid,
            'customers_customer_id' => null,
            'menu_id' => $order_details['category_id'],
            'quantity' => $order_details['quantity'],
            'food_priced_amount' => ($order_details['category_id'] == 1) ? 10 : 20,
        ]);

        //add order payment info
        OrderForNonCustomer::create([
            'receiver_name' => $order_details['receiver_name'],
            'orders_order_id' => $order->order_id,
            'receiver_location' => $order_details['delivery_location'],
        ]);
        Redis::del('select:'.$this->session_id);
        Redis::del($this->session_id);
    }
}
