<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryLocation extends Model
{
    protected $primaryKey = "delivery_location_id";
    protected $fillable = ['location_name', 'delete_status'];

//    Specify relationship
}
