<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $table = 'menus';

    protected $primaryKey = 'menu_id';
    protected $fillable = ['food_name', 'food_type', 'food_price', 'short_description',
        'long_description', 'food_image_path', 'menu_status', 'delete_status'];
    public $timestamps = true;

    public function menu (): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menu_category () : HasMany
    {
        return $this->hasMany(MenuCategory::class, 'menu_categories_menu_category_id', 'menu_catefory_id');
    }

    // public function users(){
    //     return $this->belongsTo('App\User');
    // }
}
