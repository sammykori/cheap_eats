<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderForNonCustomer extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'order_for_non_customer_id';
    protected $fillable = ['receiver_name', 'orders_order_id',
        'receiver_location'];

    public function order () : BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_for_non_customers_order_for_non_customer_id', 'order_for_non_customer_id');
    }
}
