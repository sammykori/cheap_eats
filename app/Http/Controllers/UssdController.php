<?php

namespace App\Http\Controllers;

use Illuminate\Cache\RedisStore;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Order;
use App\Menu;
use App\Customer;
use App\CheapsHandler;

class UssdController extends Controller
{
    use UssdMenuTrait;
    use SmsTrait;
    use PaymentAPI;
    protected    $amount = 10;

    public function ussdRequestHandler(Request $request)
    {
        $sessionId   = $request["sessionId"];
        $serviceCode = $request["serviceCode"];

        $cheaps = new CheapsHandler;
        $phone_number = $request->MSISDN;
        $customer_interaction = $request->USERDATA;
        $message_type = $request->MSGTYPE;
        $user_id = $request->USERID;

//        $redis = app()->make('redis');
//        $redis->set('name', 'kofi');
        Redis::lpush('name', 4);
//        $redis = Redis::set('name', 'Cole');

        $customer_data = [];

        if ($message_type)
        {
            $customer_data = $cheaps->validate_customer($phone_number, $message_type);
        }

        if ($customer_data != null)
        {

        }
        else
        {
            return $this->handleNewUser($user_id, $phone_number, $customer_interaction,$message_type);
        }




//        if(Customer::where('phone', $phone)->exists()){
//            // Function to handle already registered users
//            $name = Customer::where('phone', $phone)->pluck('name');
//            $this->handleReturnUser($text, $phone, $name[0]);
//        }else {
//             // Function to handle new users
//             $this->handleNewUser($text, $phone);
//        }
    }


    public function handleNewUser($user_id, $phone_number, $customer_interaction, $message_type)
    {
//        $ussd_string_exploded = explode ("*",$ussd_string);
        $cheaps = new CheapsHandler;
        $session_id = base64_encode($phone_number);
        $session_data = [];
        $cheaps_new_customer_response = [
            "OPTION_ONE" => "Enter your name. (E.g. Samuel Kori)",
            "OPTION_TWO" => "Menu\n1. Worker Menu\n2. Boss Menu",
            "OPTION_THREE" => "For more information\nPlease contact 0542857108\nCome Again"
        ];

        if ($message_type)
        {
            Redis::rpush($session_id, "");
            return $cheaps->handleUSSDresponse($user_id,$phone_number, $this->newUserMenu(), $message_type);
        }
        Redis::rpush($session_id, $customer_interaction);
//        $session_data = $cheaps->manage_customer_session($session_id);
//        Log::info(json_encode($session_data). " Before session is overwritted");
        if (Redis::exists($session_id))
        {
            $session_data = Redis::lrange($session_id, 0, -1);
        }

        Log::info(json_encode($session_data). " session data");
        if (count($session_data) > 0)
        {
            switch ($session_data[1])
            {
                case 1:
                    if (count($session_data) == 2)
                    {
                        return $cheaps->handleUSSDresponse($user_id,$phone_number,
                        $cheaps_new_customer_response["OPTION_ONE"], true);
                    }

                    if (count($session_data) == 3 && empty($session_data[2]))
                    {
                        $error_message = "CHEAP EATS\nSorry invalid name or no name entered";
                        $error_message .= "\nTry Again! :)";
                        return $cheaps->handleUSSDresponse($user_id,$phone_number,
                            $error_message, false);
                    }

                    if (count($session_data) == 3 && !empty($session_data[2]))
                    {
                        $name_parts = explode(" ", $session_data[2]);
                        $first_name = "";
                        $last_name = "";
                        if (count($name_parts) > 1)
                        {
                            $first_name = $cheaps->format_user_input($name_parts[0]);
                            $last_name = $cheaps->format_user_input($name_parts[1]);
                        }
                        else
                        {
                            $first_name = $cheaps->format_user_input($name_parts[0]);
                            $last_name = $cheaps->format_user_input($name_parts[0]);
                        }

                        $customer_created = Customer::create([
                            "customer_first_name" => $first_name,
                            "customer_last_name" => $last_name,
                            "phone_number" => $phone_number
                        ]);

//                        array_push($session_data, [
//                            "customer_id" =>  $customer_created->customer_id
//                        ]);
                        Redis::rpush($session_id, $customer_created->customer_id);
                        return $cheaps->handleUSSDresponse($user_id,$phone_number,
                            $this->officeList($first_name)["data"], true);
                    }

                    if (count($session_data) > 3)
                    {
                        Customer::where('delete_status', 'NOT DELETED')
                            ->where('customer_id', $session_data[3])
                            ->update([
                                "office_location" => $this->officeList("")["location"]
                                [$session_data[3] - 1]
                            ]);
                        $success_message = "Registered Successfully!\nWelcome to Cheaps dial Cheap Code\n";
                        $success_message .= "again for your personalized menu";
                        Redis::del($session_id);
                        return $cheaps->handleUSSDresponse($user_id, $phone_number, $success_message, false);
                    }

                    break;
                case 2:

                    if (count($session_data) == 2)
                    {
                        return $cheaps->handleUSSDresponse($user_id,$phone_number,
                        $cheaps_new_customer_response["OPTION_TWO"], true);
                    }

                    if ($session_data[2] == 2)
                    {
                        //Worker menu
                        return $cheaps->handleUSSDresponse($user_id, $phone_number, $this->workerMenu(), true);
                    }
                    else if ($session_data[2] == 3)
                    {
                        // Boss menu
                        return $cheaps->handleUSSDresponse($user_id, $phone_number, $this->bossuMenu(), true);
                    }
                    break;
                case 3:
                    $this->handleUSSDresponse($user_id,$phone_number, $cheaps_new_customer_response["OPTION_THREE"], true);
                    break;
            }
        }



        return  $this->handleUSSDresponse($user_id, $phone_number, "Hello World", $message_type);
//        switch ($level) {
//            case ($level == 1 && !empty($ussd_string)):
//                if ($ussd_string_exploded[0] == "1") {
//                    // If user selected 1 send them to the registration menu
//                    $this->ussd_proceed("Please enter your full name. \n eg: Jane Doe");
//                } else if ($ussd_string_exploded[0] == "2") {
//                    //If user selected 2, send them the information
//                    $this->foodMenu('');
//                } else if ($ussd_string_exploded[0] == "3") {
//                    //If user selected 3, exit
//                    $this->ussd_stop("For more information please call");
//                }
//            break;
//            case 2:
//                if($ussd_string_exploded[0] == "1" && !empty($ussd_string_exploded[1])){
//                    $this->officeList();
//                }
//                else if($ussd_string_exploded[0] == "2" && !empty($ussd_string_exploded[1])){
//                    if($ussd_string_exploded[1] == "1"){
//                        $this->workerMenu();
//                    }
//                    else if($ussd_string_exploded[1] == "2"){
//                        $this->bossuMenu();
//                    }
//                }
//
//            break;
//            case 3:
//                if($ussd_string_exploded[0] == "1" && !empty($ussd_string_exploded[2])){
//                    if($this->ussdRegister($ussd_string_exploded[1],$ussd_string_exploded[2], $phone) == "success"){
//                        $name = Customer::where('phone', $phone)->pluck('name');
//                        $this->ussd_proceed("Welcome to Cheaps!\nDial the Cheap Code again for your personalized menu");
//                    }
//
//                }else if($ussd_string_exploded[0] == "2" && !empty($ussd_string_exploded[2])){
//                    $this->ussd_proceed("Quantity preferred,\n NB: Quantity more than 10 will take more time \n");
//                }
//            break;
//            case 4:
//                if($ussd_string_exploded[0] == "2" && !empty($ussd_string_exploded[3])){
//                    $this->amount *= (int)$ussd_string_exploded[3];
//                    $this->ussd_proceed("Please enter full name of Contact person \n");
//                }
//            break;
//            case 5:
//                if($ussd_string_exploded[0] == "2" && !empty($ussd_string_exploded[4])){
//                    $this->officeList();
//                }
//            break;
//            case 6:
//                if($ussd_string_exploded[0] == "2" && !empty($ussd_string_exploded[5])){
//                    $int = (int)$ussd_string_exploded[3];
//                    $this->amount *= $int;
//                    $this->ussd_proceed("Name: ".$ussd_string_exploded[4]." (" .$phone.") \n Order: ".$ussd_string_exploded[2]." (". $ussd_string_exploded[3].") \n  Price: GHS ".$this->amount." \n 1. Confirm \n 2. Cancel" );
//                }
//            break;
//            case 7:
//                if($ussd_string_exploded[0] == "2" && !empty($ussd_string_exploded[6])){
//                    if($ussd_string_exploded[6] == "1"){
//                        $uuid = $this->generateUuid();
//                        $int = (int)$ussd_string_exploded[3];
//                        $this->amount *= $int;
//                        $status = $this->requestToPay($uuid, $this->amount, $phone);
//                        if($status == "202"){
//                            $response = $this->requestPayStatus($uuid);
//                                if($response->status = "SUCCESSFUL"){
//                                    $this->placeOrder($phone, $ussd_string_exploded, $uuid);
//                                }
//                            $this->ussd_stop("Order Confirmed ".$response->status);
//
//                        }
//                        else{
//                            $this->ussd_stop("User Momo Account not Active\n".$uuid);
//                        }
//                    }else if($ussd_string_exploded[6] == "2"){
//                        $this->ussd_stop("Thank you. Do come again ;)");
//                    }
//                }
//            break;
//            // N/B: There are no more cases handled as the following requests will be handled by return user
//        }
    }

    public function generateUuid(){
        return (string) Str::uuid();
    }

	public function handleReturnUser($ussd_string, $phone, $name)
	{
		$ussd_string_exploded = explode ("*",$ussd_string);

		// Get menu level from ussd_string reply
		$level = count($ussd_string_exploded);

		if(empty($ussd_string) or $level == 0) {
			$this->foodMenu($name); // show the home/first menu
		}

		switch ($level) {
			case ($level == 1 && !empty($ussd_string)):
				if ($ussd_string_exploded[0] == "1") {
					$this->workerMenu();
				} else if ($ussd_string_exploded[0] == "2") {
					//If user selected 2, end session
					$this->bossuMenu();
				}
			break;
			case 2:
				if(!empty($ussd_string_exploded[1])){
                    $this->ussd_proceed("Quantity preferred,\n NB: Quantity more than 10 will take more time \n");
                }
			break;
			case 3:
				if (!empty($ussd_string_exploded[2])) {
					$this->ussd_proceed("1. For Self\n2. For other");
				}
            break;
            case 4:
				if (!empty($ussd_string_exploded[3]) && $ussd_string_exploded[3] == "1") {
					$int = (int)$ussd_string_exploded[2];
                    $this->amount *= $int;
                    $this->ussd_proceed("Name: ".$name." (" .$phone.") \n Order: ".$ussd_string_exploded[1]." (". $ussd_string_exploded[2].") \n  Price: GHS ".$this->amount." \n 1. Confirm \n 2. Cancel" );
                }
                else if(!empty($ussd_string_exploded[3]) && $ussd_string_exploded[3] == "2"){
                    $this->ussd_proceed("Full name of contact person");
                }
            break;
            case 5:
                if($ussd_string_exploded[3] == "1" && !empty($ussd_string_exploded[4])){
                    if($ussd_string_exploded[4] == "1"){
                        $uuid = $this->generateUuid();
                        $int = (int)$ussd_string_exploded[2];
                        $this->amount *= $int;
                        $loc = Customer::where('phone', $phone)->pluck('office');
                        $menu = Menu::where([['type', $ussd_string_exploded[0]], ['status', 'available']])->pluck('name');
                        $m = (int)$ussd_string_exploded[1]-1;
                        $status = $this->requestToPay($uuid, $this->amount, $phone);
                        if($status == "202"){
                            $response = $this->requestPayStatus($uuid);
                                if($response->status = "SUCCESSFUL"){
                                    $order = new Order;
                                    $order->uuid = $uuid;
                                    $order->name = $name;
                                    $order->phone = $phone;
                                    $order->menu_id = $menu[$m];
                                    $order->quantity = $ussd_string_exploded[2];
                                    $order->amount = $this->amount;
                                    $order->location = $loc[0];
                                    $order->save();
                                }
                            $this->ussd_stop("Order Confirmed ".$response->status);

                        }
                        else{
                            $this->ussd_stop("User Momo Account not Active\n".$uuid);
                        }
                    }else if($ussd_string_exploded[4] == "2"){
                        $this->ussd_stop("Thank you. Do come again ;)");
                    }
                }
				else if ($ussd_string_exploded[3] == "2" && !empty($ussd_string_exploded[4])) {
					$this->officeList();
				}
            break;
            case 6:
				if (!empty($ussd_string_exploded[5])) {
					$int = (int)$ussd_string_exploded[2];
                    $this->amount *= $int;
                    $this->ussd_proceed("Name: ".$ussd_string_exploded[4]." (" .$phone.") \n Order: ".$ussd_string_exploded[1]." (". $ussd_string_exploded[2].") \n  Price: GHS ".$this->amount." \n 1. Confirm \n 2. Cancel" );
				}
            break;
            case 7:
                if(!empty($ussd_string_exploded[6])){
                    if($ussd_string_exploded[6] == "1"){
                        $uuid = $this->generateUuid();
                        $int = (int)$ussd_string_exploded[2];
                        $this->amount *= $int;
                        $menu = Menu::where([['type', $ussd_string_exploded[0]], ['status', 'available']])->pluck('name');
                        $m = (int)$ussd_string_exploded[1] -1;
                        $status = $this->requestToPay($uuid, $this->amount, $phone);
                        if($status == "202"){
                            $response = $this->requestPayStatus($uuid);
                                if($response->status = "SUCCESSFUL"){
                                    $order = new Order;
                                    $order->uuid = $uuid;
                                    $order->name = $ussd_string_exploded[4];
                                    $order->phone = $phone;
                                    $order->menu_id = $menu[$m];
                                    $order->quantity = $ussd_string_exploded[2];
                                    $order->amount = $this->amount;
                                    $order->location = $ussd_string_exploded[5];
                                    $order->save();
                                }
                            $this->ussd_stop("Order Confirmed ".$response->status);

                        }
                        else{
                            $this->ussd_stop("User Momo Account not Active\n".$uuid);
                        }
                    }else if($ussd_string_exploded[6] == "2"){
                        $this->ussd_stop("Thank you. Do come again ;)");
                    }
                }
				else if ($ussd_string_exploded[3] == "2" && !empty($ussd_string_exploded[4])) {
					$this->officeList();
				}
            break;
		}
    }
    public function ussd_proceed($ussd_text) {
        echo "CON $ussd_text";
      }
      public function ussd_stop($ussd_text) {
        echo "END $ussd_text";
      }

      public function ussdRegister($name, $office, $phone)
      {
        //   $input = explode(",",$details);//store input values in an array
          $full_name = $name;//store full name
          $location = $office;

          $user = new Customer;
          $user->name = $full_name;
          $user->phone = $phone;
          // You should encrypt the pin
          $user->office = $location;
          $user->save();

          return "success";
      }

      public function placeOrder($phone, $ussd, $uuid)
      {
        $menu = Menu::where([['type', $ussd[1]], ['status', 'available']])->pluck('name');
        $m = (int)$ussd[2]-1;
          $order = new Order;
          $order->uuid = $uuid;
          $order->name = $ussd[4];
          $order->phone = $phone;
          $order->menu_id = $menu[$m];
          $order->quantity = $ussd[3];
          $order->amount = $this->amount;
          $order->location = $ussd[5];
          $order->save();

          return "success";
      }

      /**
       * Handles Login Request
       */
    //   public function ussdLogin($details, $phone)
    //   {
    //       $user = User::where('phone', $phone)->first();

    //       if ($user->pin == $details ) {
    //           return "Success";
    //       } else {
    //           return $this->ussd_stop("Login was unsuccessful!");
    //       }
    //   }


    private function handleUSSDresponse ($USER_ID, $customer_phone_number, $cheaps_message, $message_type)
    {
//        Log::info("response handler called");
//        header('Content-type: application/json');
        $res = [
            'USERID' => $USER_ID,
            'MSISDN' => $customer_phone_number,
            'MSG' => $cheaps_message,
            'MSGTYPE' => $message_type
        ];
        Log::info(json_encode($res));
        return json_encode($res);
    }
}
