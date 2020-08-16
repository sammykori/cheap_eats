<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    protected $fillable = ['uuid', 'customers_customer_id', 'menu_id', 'quantity', 'food_priced_amount'];
    public $timestamps = true;

    // public function users(){
    //     return $this->belongsTo('App\User');
    // }

    public function  customer () : BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customers_customer_id', 'customer_id')
            ->where('delete_status','=', 'NOT DELETED');
    }

    public function menu () : HasMany
    {
        return $this->hasMany(Menu::class, 'menus_menu_id', 'menu_id')
            ->where('delete_status','=', 'NOT DELETED');
    }

    public function order_payment () : HasOne
    {
        return $this->hasOne(Order::class, 'orders_order_id', 'order_id')
            ->where('delete_status', '=', 'NOT DELETED');
    }

    public function order_delivery () : HasOne
    {
        return $this->hasOne(Order_delivery::class, 'orders_order_id', 'order_id')
            ->where('delete_status', '=', 'NOT DELETED');
    }

    public  function order_for_no_customer () : HasMany
    {
        return $this->hasMany(OrderForNonCustomer::class, 'order_for_non_customers_order_for_non_customer_id', 'order_for_non_customer_id');
    }
}
