<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryLocation extends Model
{
    use SoftDeletes;

    protected $primaryKey = "delivery_location_id";
    protected $fillable = ['location_name'];

//    Specify relationship
}
