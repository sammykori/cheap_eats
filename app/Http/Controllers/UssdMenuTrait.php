<?php
namespace App\Http\Controllers;
use App\Menu;
use Illuminate\Support\Facades\Log;

trait UssdMenuTrait{

    public function newUserMenu(){
        $start  = "Welcome to CHEAP->EATS\n";
        $start .= "1. Register\n";
        $start .= "2. Menu\n";
        $start .= "3. Exit";
        return $start;
//        $this->ussd_proceed($start);
    }
    public function officeList($customer_name){
        $office_locations = ["SSNIT", "Marina Super Market",
            "Nestle Square", "National Communications Authority"];
        $office = "Select delivery location $customer_name\n";
        $office .= "1. $office_locations[0]\n";
        $office .= "2. $office_locations[1]\n";
        $office .= "3. $office_locations[2]\n";
        $office .= "4. $office_locations[3]";
        return ["location" => $office_locations, "data" => $office];
//        $this->ussd_proceed($office);
    }
    public function foodMenu($name){
        $food = "What are feeling for today ". ucwords($name). ",\n";
        $food .= "1. Worker Meal (GHS 10.00)\n";
        $food .= "2. Bossu Meal (GHS 20.00)\n";
//        $this->ussd_proceed($food);
        return $food;
    }

    public function workerMenu(){
        $menus = Menu::where([['food_type', 'worker menu'], ['menu_status', 'available']])->pluck('food_name', 'menu_id');
        $bf = "Worker meal  All meals cost GHS 10.00\n";
        $i = 0;
        $keys = [];
        Log::info(json_encode($menus) . " for menu");
//        if(count($menus) > 0){
//            foreach ($menus as $key => $menu) {
//                $i++;
//                $bf .= "$i. $menu \n";
//                $keys[$i] = $key;
//            }
//        }
        return ["data" => $bf, "menu" => $menus, "keys" => $keys];
//        $this->ussd_proceed($bf);
    }
    public function bossuMenu(){
        $menu = Menu::where([['food_type', 'bossu menu'], ['menu_status', 'available']])->pluck('food_name', 'menu_id');
        $lunch = "Bossu meal | All meals cost GHS 20.00\n ";
        $i = 0;
        if(count($menu) > 0){
            foreach ($menu as $menu) {
                $i++;
                $lunch .= "$i. $menu \n";
            }
        }
        return ["data" => $lunch, "menu" => $menu];
//        $this->ussd_proceed($lunch);
    }
}
