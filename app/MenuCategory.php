<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuCategory extends Model
{
    protected $primaryKey = 'menu_category_id';
    protected $fillable = ['category_name', 'category_description', 'category_status', 'delete_status'];

    public function menu () : BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_categories_menu_category_id', 'menu_catefory_id');
    }

}
