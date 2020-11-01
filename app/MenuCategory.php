<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuCategory extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'menu_category_id';
    protected $fillable = ['category_name', 'category_description', 'category_status'];

    public function menu () : BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_categories_menu_category_id', 'menu_catefory_id');
    }

}
