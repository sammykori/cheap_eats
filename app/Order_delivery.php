<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order_delivery extends Model
{
    protected $primaryKey = "order_delivery_id";
    protected $fillable = ['orders_order_id', 'delivery_token', 'delivery_type', 'delivery_location', 'departure_time', 'delivery_time', 'delete_status'];

    public function order () : HasOne
    {
        return $this->hasOne(Order::class, 'orders_order_id', 'order_id')
            ->where('delete_status', '=', 'NOT DELETED');
    }
}
