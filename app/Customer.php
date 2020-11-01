<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $primaryKey = "customer_id";
    protected $fillable = ['customer_first_name', 'customer_last_name', 'office_location', 'phone_number',
        'account_status'];

    public function orders () : HasMany
    {
        return $this->hasMany(Order::class, 'customers_customer_id', 'customer_id')
            ->where('delete_status', '=', 'NOT DELETED');
    }

}
