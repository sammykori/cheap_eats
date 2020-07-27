<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Order;

class UssdController extends Controller
{
    use UssdMenuTrait;
    use SmsTrait;
    private $amount = 10;

    public function ussdRequestHandler(Request $request)
    {
        $sessionId   = $request["sessionId"];
        $serviceCode = $request["serviceCode"];
        $phone       = $request["phoneNumber"];
        $text        = $request["text"];

        header('Content-type: text/plain');

        if(User::where('phone', $phone)->exists()){
            // Function to handle already registered users
            $name = User::where('phone', $phone)->pluck('name');
            $this->handleReturnUser($text, $phone, $name[0]);
        }else {
             // Function to handle new users
             $this->handleNewUser($text, $phone);
        }
    }
    public function handleNewUser($ussd_string, $phone)
    {
        $ussd_string_exploded = explode ("*",$ussd_string);

        // Get menu level from ussd_string reply
        $level = count($ussd_string_exploded);

        if(empty($ussd_string) or $level == 0) {
            $this->newUserMenu(); // show the home menu
        }

        switch ($level) {
            case ($level == 1 && !empty($ussd_string)):
                if ($ussd_string_exploded[0] == "1") {
                    // If user selected 1 send them to the registration menu
                    $this->ussd_proceed("Please enter your full name. \n eg: Jane Doe");
                } else if ($ussd_string_exploded[0] == "2") {
                    //If user selected 2, send them the information
                    $this->foodMenu('');
                } else if ($ussd_string_exploded[0] == "3") {
                    //If user selected 3, exit
                    $this->ussd_stop("Thank you for reaching out to SampleUSSD.");
                }

            break;
            case 2:
                if($ussd_string_exploded[0] == "1" && !empty($ussd_string_exploded[1])){
                    $this->officeList();
                }
                else if($ussd_string_exploded[0] == "2" && !empty($ussd_string_exploded[1])){
                    if($ussd_string_exploded[1] == "1"){
                        $this->bfMenu();
                    }
                    else if($ussd_string_exploded[1] == "2"){
                        $this->lunchMenu();
                    }
                }

            break;
            case 3:
                if($ussd_string_exploded[0] == "1" && !empty($ussd_string_exploded[2])){
                    if($this->ussdRegister($ussd_string_exploded[1],$ussd_string_exploded[2], $phone) == "success"){
                        $name = User::where('phone', $phone)->pluck('name');
                        $this->foodMenu($name[0]);
                    }
                }else if($ussd_string_exploded[0] == "2" && !empty($ussd_string_exploded[2])){
                    $this->ussd_proceed("Quantity preferred,\n NB: Quantity more than 10 will take more time \n");
                }
            break;
            case 4:
                if($ussd_string_exploded[0] == "1" && !empty($ussd_string_exploded[3])){
                    if($ussd_string_exploded[3] == "1"){
                        $this->bfMenu();
                    }
                    else if($ussd_string_exploded[3] == "2"){
                        $this->lunchMenu();
                    }
                }
                else if($ussd_string_exploded[0] == "2" && !empty($ussd_string_exploded[3])){
                    $this->amount *= (int)$ussd_string_exploded[3];
                    $this->ussd_proceed("Please enter full name of Contact person \n");
                }
            break;
            case 5:
                if($ussd_string_exploded[0] == "1" && !empty($ussd_string_exploded[4])){
                    $this->ussd_proceed("Quantity preferred,\n NB: Quantity more than 10 will take more time \n");
                }
                else if($ussd_string_exploded[0] == "2" && !empty($ussd_string_exploded[4])){
                    $this->officeList();
                }
            break;
            case 6:
                if($ussd_string_exploded[0] == "1" && !empty($ussd_string_exploded[5])){
                    $this->ussd_proceed("1. For Self \n 2. For other Person \n (Enter name and office)");
                }
                else if($ussd_string_exploded[0] == "2" && !empty($ussd_string_exploded[5])){
                    $this->ussd_proceed("Name: ".$ussd_string_exploded[4]." (" .$phone.") \n Order: ".$ussd_string_exploded[2]." (". $ussd_string_exploded[3].") \n  Price: ".$this->amount." \n 1. Confirm \n 2. Cancel" );
                }
            break;
            case 7:
                if($ussd_string_exploded[0] == "1" && !empty($ussd_string_exploded[5])){
                    if($ussd_string_exploded[5] == "2"){
                        $name = User::where('phone', $phone)->pluck('name');
                        $this->ussd_proceed("Name: ".$name." (" .$phone.") \n Order: ".$ussd_string_exploded[3]." (". $ussd_string_exploded[4].") \n  Price: ".$this->amount." \n 1. Confirm \n 2. Cancel" );
                    }
                    else if($ussd_string_exploded[5] == "1"){
                        $this->ussd_stop("Thank you. Do come again ;)");
                    }
                }
                else if($ussd_string_exploded[0] == "2" && !empty($ussd_string_exploded[6])){
                    if($ussd_string_exploded[6] == "1"){
                        if($this->placeOrder($phone, $ussd_string_exploded) == "success"){
                            $this->ussd_stop("Proceed with payment to complete order");
                        }
                    }else if($ussd_string_exploded[6] == "2"){
                        $this->ussd_stop("Thank you. Do come again ;)");
                    }
                }
            break;
            // N/B: There are no more cases handled as the following requests will be handled by return user
        }
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
					$this->bfMenu();
				} else if ($ussd_string_exploded[0] == "2") {
					//If user selected 2, end session
					$this->lunchMenu();
				}
			break;
			case 2:
				if ($this->ussdLogin($ussd_string_exploded[1], $phone) == "Success") {
					$this->servicesMenu();
				}
			break;
			case 3:
				if ($ussd_string_exploded[2] == "1") {
					$this->ussd_stop("You will receive an sms shortly.");
					$this->sendText("You have successfully subscribed to updates from SampleUSSD.",$phone);
				} else if ($ussd_string_exploded[2] == "2") {
					$this->ussd_stop("You will receive more information on SampleUSSD via sms shortly.");
					$this->sendText("This is a subscription service from SampleUSSD.",$phone);
				} else if ($ussd_string_exploded[2] == "3") {
					$this->ussd_stop("Thanks for reaching out to SampleUSSD.");
				} else {
					$this->ussd_stop("Invalid input!");
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

          $user = new User;
          $user->name = $full_name;
          $user->phone = $phone;
          // You should encrypt the pin
          $user->office = $location;
          $user->save();

          return "success";
      }

      public function placeOrder($phone, $ussd)
      {
          $order = new Order;
          $order->name = $ussd[4];
          $order->phone = $phone;
          $order->menu_id = $ussd[2];
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
}
