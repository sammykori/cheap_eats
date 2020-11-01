<?php


namespace App\GraphQL\Queries;


use App\Order;

class OrdersQuery
{
    public function cheaps_orders ($root, array $args) {
        return Order::all();
    }
}
