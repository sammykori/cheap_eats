<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order_payment extends Model
{
    use SoftDeletes;

    protected $primaryKey = "order_payment_id";
    protected $fillable = ['orders_order_id', 'payment_status', 'payment_type'];

    public function order () : BelongsTo
    {
        return $this->belongsTo(Order::class, 'orders_order_id', 'order_id')
            ->where('delete_status', '=', 'NOT DELETED');
    }
}
