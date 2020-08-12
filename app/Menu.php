<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';

    public $primaryKey = 'id';

    public $timestamps = true;

    // public function users(){
    //     return $this->belongsTo('App\User');
    // }
}
