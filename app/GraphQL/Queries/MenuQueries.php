<?php


namespace App\GraphQL\Queries;


use App\Menu;

class MenuQueries
{
    public  function cheaps_menus ($root, array $args) {
        return Menu::all();
    }

    public function cheaps_menu ($root, array $args) {
        return Menu::find($args['menu_id']);
    }
}
