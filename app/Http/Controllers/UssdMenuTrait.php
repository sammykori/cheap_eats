<?php
namespace App\Http\Controllers;

trait UssdMenuTrait{

    public function newUserMenu(){
        $start  = "Welcome to CHEAP->EATS\n";
        $start .= "1. Register\n";
        $start .= "2. Menu\n";
        $start .= "3. Exit";
        $this->ussd_proceed($start);
    }
    public function servicesMenu(){
        $serve = "What service are you looking for?\n";
        $serve .= "1. Subscribe to updates\n";
        $serve .= "2. Information on the service\n";
        $serve .= "3. Logout";
        $this->ussd_proceed($serve);
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
        $food .= "1. Breakfast\n";
        $food .= "2. Lunch\n";
        $this->ussd_proceed($food);
    }
    public function bfMenu(){
        $bf = "Breakfast is the most important meal of the day \n";
        $bf .= "1. Special Kooko with Koose & Bread \n";
        $bf .= "2. Rich Tea with Bread & Egg\n";
        $bf .= "2. Special Oats with Bread & Egg\n";
        $this->ussd_proceed($bf);
    }
    public function lunchMenu(){
        $lunch = "Lunch breaks work wonders \n";
        $lunch .= "1. Waakye Special with Fish\n";
        $lunch .= "2. Millet Kenkey with Red Fish\n";
        $lunch .= "2. Special Jollof with Egg\n";
        $this->ussd_proceed($lunch);
    }
}
