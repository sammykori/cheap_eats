<?php


namespace App\GraphQL\Mutations;


use App\Menu;
use Illuminate\Support\Facades\Log;

class MenuMutations
{
    public function create_menu ($root, array $args) {
        return Menu::create(collect($args)->except("directive")->toArray());
    }
}
