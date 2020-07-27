<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    public $primaryKey = 'id';

    public $timestamps = true;

    // public function users(){
    //     return $this->belongsTo('App\User');
    // }
}
