<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order_payment extends Model
{
    protected $primaryKey = "order_payment_id";
    protected $fillable = ['orders_order_id', 'payment_status', 'payment_type', 'delete_status'];

    public function order () : BelongsTo
    {
        return $this->belongsTo(Order::class, 'orders_order_id', 'order_id')
            ->where('delete_status', '=', 'NOT DELETED');
    }
}
