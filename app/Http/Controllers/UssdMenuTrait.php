<?php
namespace App\Http\Controllers;
use App\Menu;

trait UssdMenuTrait{

    public function newUserMenu(){
        $start  = "Welcome to CHEAP->EATS\n";
        $start .= "1. Register\n";
        $start .= "2. Menu\n";
        $start .= "3. Exit";
        return $start;
//        $this->ussd_proceed($start);
    }
    public function officeList(){
        $office = "Select delivery location\n";
        $office .= "1. SSNIT\n";
        $office .= "2. Marina Super Market\n";
        $office .= "3. Nestle Square\n";
        $office .= "4. National Communications Authority";
        $this->ussd_proceed($office);
    }
    public function foodMenu($name){
        $food = "What are feeling for today ".ucwords($name). ",\n";
        $food .= "1. Worker Meal (GHS 10.00)\n";
        $food .= "2. Bossu Meal (GHS 20.00)\n";
        $this->ussd_proceed($food);
    }
    public function workerMenu(){
        $menu = Menu::where([['type', '1'], ['status', 'available']])->pluck('name');
        $bf = "Worker meal | All meals cost GHS 10.00\n";
        $i = 0;
        if(count($menu) > 0){
            foreach ($menu as $menu) {
                $i++;
                $bf .= "$i. $menu \n";
            }
        }
        return $bf;
//        $this->ussd_proceed($bf);
    }
    public function bossuMenu(){
        $menu = Menu::where([['type', '2'], ['status', 'available']])->pluck('name');
        $lunch = "Bossu meal | All meals cost GHS 20.00\n ";
        $i = 0;
        if(count($menu) > 0){
            foreach ($menu as $menu) {
                $i++;
                $lunch .= "$i. $menu \n";
            }
        }
        return $lunch;
//        $this->ussd_proceed($lunch);
    }
}
