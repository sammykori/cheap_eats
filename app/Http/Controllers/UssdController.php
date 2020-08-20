<?php

namespace App\Http\Controllers;

use App\Jobs\OrderForRegisterdCustomers;
use App\Jobs\OrderJob;
use App\Order_delivery;
use App\OrderForNonCustomer;
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
        $cheaps = new CheapsHandler;
        $phone_number = $request->MSISDN;
        $customer_interaction = $request->USERDATA;
        $message_type = $request->MSGTYPE;
        $user_id = $request->USERID;
        $connection = [
            'user_id' => $user_id,
            'phone_number' => $phone_number,
            'message_type' => $message_type,
            'isregistered' => 0
        ];
        Log::info("Begin's here --> ");
        $customer_data = [];
        $session_id = base64_encode($phone_number);
        Redis::hset($session_id, "customer_interaction", $customer_interaction);

//        __uninitialized__ session
        if (strcmp($customer_interaction, "User timeout") == 0)
        {
                Log::info("I am here at time out");
                Redis::del('select:'.$session_id);
                Redis::del($session_id);
                return true;
        }
//        __ initialize__ session
        $customer_data = $cheaps->validate_customer($phone_number, $message_type);
        if (Redis::hexists($session_id, "customer_profile") && $connection['message_type']) {
            Redis::hset($session_id, "customer_interaction", 2);
            Redis::hset($session_id, "isregistered", 1);
            $connection['isregistered'] = 1;
        } else if (!Redis::hexists($session_id, "customer_profile")) {
            Redis::hset($session_id, "isregistered", 0);
        }
        return $this->handleNewUser($connection);
    }


    public function handleNewUser($connection)
    {
        $cheaps = new CheapsHandler;
        $session_id = base64_encode($connection['phone_number']);
        $session_data = [];
        $cheaps_new_customer_response = [
            "OPTION_ONE" => "Enter your name. (E.g. Jane Doe)",
            "OPTION_TWO" => "Menu\n1. Worker Menu\n2. Boss Menu",
            "OPTION_THREE" => "For more information\nPlease contact 0542857108\nCome Again"
        ];

        $connection['isregistered'] = Redis::hget($session_id, 'isregistered');

        if ($connection['message_type'] && $connection['isregistered'] == 0) return $cheaps->handleUSSDresponse($connection, $this->newUserMenu());

        Redis::rpush('select:'.$session_id, Redis::hget($session_id, 'customer_interaction'));

        if (Redis::exists('select:'.$session_id)) {
            $session_data = Redis::lrange('select:'.$session_id, 0, -1);
            Redis::hset($session_id, 'selection', $session_data);
            Redis::expire('select:'.$session_id, 1500);
            Redis::expire($session_id, 1500);
        }

        if (count($session_data) > 0)
        {
            switch ($session_data[0])
            {
                case 1:
                    if (count($session_data) == 1) {
                        $connection['message_type'] = true;
                        return $cheaps->handleUSSDresponse($connection, $cheaps_new_customer_response["OPTION_ONE"]);
                    }

                    if (count($session_data) == 2 && empty($session_data[1])) {
                        $connection['message_type'] = false;
                        $cheaps->clear_customer_session($session_id);
                        $error_message = "CHEAP EATS\nSorry invalid name or no name entered";
                        $error_message .= "\nTry Again! :)";
                        return $cheaps->handleUSSDresponse($connection, $error_message);
                    }

                    if (count($session_data) == 2 && !empty($session_data[1]))
                    {
                        $name_parts = explode(" ", $session_data[1]);
                        $first_name = "";
                        $last_name = "";
                        if (count($name_parts) > 1) {
                            $first_name = $cheaps->format_user_input($name_parts[0]);
                            $last_name = $cheaps->format_user_input($name_parts[1]);
                        } else {
                            $first_name = $cheaps->format_user_input($name_parts[0]);
                            $last_name = $cheaps->format_user_input($name_parts[0]);
                        }

                        $customer_created = Customer::create([
                            "customer_first_name" => $first_name,
                            "customer_last_name" => $last_name,
                            "phone_number" => $connection['phone_number']
                        ]);
                        $connection['message_type'] = true;
                        Redis::rpush($session_id, $customer_created->customer_id);
                        return $cheaps->handleUSSDresponse($connection, $this->officeList($first_name)["data"]);
                    }

                    if (count($session_data) > 2)
                    {
                        if ($session_data[2] > 3) {
                            $selection_message = "CHEAP EATS\n";
                            $selection_message .= "Wrong choice made.\n Select again from your next session.";
                            Redis::rpop($session_id);
                            $connection['message_type'] = false;
                            return $cheaps->handleUSSDresponse($connection, $selection_message);
                        }

                        Customer::where('delete_status', 'NOT DELETED')
                            ->where('customer_id', $session_data[2])
                            ->update([
                                "office_location" => $this->officeList("")["location"]
                                [$session_data[2] - 1]
                            ]);
                        $success_message = "Registered Successfully!\nWelcome to Cheaps dial Cheap Code\n";
                        $success_message .= "again for your personalized menu";
                        $cheaps->clear_customer_session($session_id);
                        $connection['message_type'] = false;
                        return $cheaps->handleUSSDresponse($connection, $success_message);
                    }

                    break;
                case 2:

                    if (count($session_data) == 1) {
                        Log::info(Redis::hget($session_id, 'isregistered'));
                        $connection['message_type'] = true;
                        $name = ($connection['isregistered'] == 1) ?  json_decode( Redis::hget($session_id, "customer_profile") , 1)
                        ["customer_first_name"] : " ";
                        return $cheaps->handleUSSDresponse($connection, $this->foodMenu($name));
                    }

                    if (count($session_data) > 1 && $session_data[1] == 1) {
                        $connection['category_id'] = 1;
                        return $this->customer_order($session_id, $session_data,
                            $cheaps_new_customer_response["OPTION_TWO"], $connection, 'worker menu');
                    } else if (count($session_data) > 1 && $session_data[1] == 2) {
                        // Boss menu
                        $connection['category_id'] = 2;
                        return $this->customer_order($session_id, $session_data,
                            $cheaps_new_customer_response["OPTION_THREE"],$connection, 'bossu menu');
                    }
                    break;
                case 3:
                    $cheaps->clear_customer_session($session_id);
                    $connection['message_type'] = false;
                    return $cheaps->handleUSSDresponse($connection, $cheaps_new_customer_response["OPTION_THREE"]);
                    break;
            }
        }
        $cheaps->clear_customer_session($session_id);
        return  $cheaps->handleUSSDresponse($connection, "Invalid input. See you soon :)");
    }

    private function  customer_order ($session_id, $session_data, $cheaps_new_customer_response, $connection,
                                      $menu_type) {
        $cheaps = new CheapsHandler;
        // menu CONTROLLER
        $size = count($session_data);
        Log::info("when entered " . count($session_data));
        Redis::hset($session_id, 'category_id', $connection['category_id']);
        $isregistered = Redis::hget($session_id, 'isregistered');
        if (count($session_data) == 2) return $cheaps->customer_order_processing_status($this->allMenu($menu_type)["data"], $connection, true);


        if (!empty($session_data[2]) && !Arr::exists($this->allMenu($menu_type)["menu"], $this->allMenu($menu_type)["keys"][$session_data[2]])) {
            if (!empty($session_data[3]) && $session_data[3] == 1) {
                Redis::rpop($session_id);
                $connection['message_type'] = true;
                return $this->handleNewUser($connection);
            } else if (!empty($session_data[3]) && $session_data[3] == 2) {
                $connection['message_type'] = true;
                return $cheaps->handleUSSDresponse($connection, $cheaps_new_customer_response);
            }
            $message = "Wrong Input\n1. Try Again\n2.Exit";
            return $cheaps->customer_order_processing_status($message, $connection, true);
        }

        if ($size == 3) {
            Redis::hset($session_id, 'food_id', $this->allMenu($menu_type)["keys"][$session_data[2]]);
            Redis::hset($session_id, 'food_name',  $this->allMenu($menu_type)["menu"]
            [$this->allMenu($menu_type)["keys"][$session_data[2]]]);
            $message = "Quantity?";
            return $cheaps->customer_order_processing_status($message, $connection, true);
        }

        if ($size == 4 && $session_data[3] <= 5) {
            Redis::hset($session_id, 'quantity', $session_data[3]);
            Log::info(Redis::hget($session_id, 'isregistered'). " registration status\n\n");
            if ($isregistered == 0) {
                $message = "Enter full name of contact person:";
                return $cheaps->customer_order_processing_status($message, $connection, true);
            }
        }

        if ($size == 4 && $session_data[3] > 5) {
            $message = "Please contact us on 0542833108 for bulk orders";
            $cheaps->clear_customer_session($session_id);
            return $cheaps->customer_order_processing_status($message, $connection, false);
        }

        //check for other input which is not a digit.
        if ($isregistered == 1) {
            Log::info("here");
            return $this->customer_registered($session_data, $connection, $cheaps,$session_id);
        }

        return $this->customer_not_registered($session_data, $connection, $cheaps, $session_id);
    }





    private function record_orders(array $order_details)
    {
        $order = Order::create([
            'uuid' => $this->generateUuid(),
            'customers_customer_id' => null,
            'menu_id' => $order_details['category_id'],
            'quantity' => $order_details['quantity'],
            'food_priced_amount' => ($order_details['category_id'] == 1) ? 10 : 20,
        ]);

        //add order payment info
        OrderForNonCustomer::create([
           'receiver_name' => $order_details['receiver_name'],
           'orders_order_id' => $order->order_id,
           'receiver_location' => $order_details['delivery_location'],
        ]);
    }

    public function generateUuid(){
        return (string) Str::uuid();
    }


    public function customer_registered ($session_data, $connection, $cheaps, $session_id) {
        $size = count($session_data);
        $cursor = $size - 1;
        Log::info($size . " size");
        Log::info("is registered ". $connection["isregistered"]);
        if ($size == 4) {
            //handle registered person's order
            $message = "1. For Self\n2. For Others";
            return $cheaps->customer_order_processing_status($message, $connection, true);
        }

        if ($size == 5) {
            //handle registered person's order
            if ($session_data[$cursor] == CheapsHandler::$CONFIRM_ORDER) {
                Redis::hset($session_id, "order_type", "SELF");
                $connection['disp_name'] = json_decode(Redis::hget($session_id, "customer_profile"), 1)["customer_first_name"];
                return $cheaps->complete_customer_order($session_id, $connection);
            } else if ($session_data[$cursor] == CheapsHandler::$CANCEL_ORDER) {
                $message = "Enter full name of contact person:";
                return $cheaps->customer_order_processing_status($message, $connection, true);
            }
        }


        if ($size == 6) {
            if ($session_data[$cursor] == CheapsHandler::$CONFIRM_ORDER) {
                // make call to mobile money payment
                //wait for response and then send this reply back to user
                $order = Redis::hgetall($session_id);
                dispatch(new OrderForRegisterdCustomers($order, $session_id, $this->generateUuid()))->delay(2); // dispatch order for
                //existing customer
                $message = "Order processed\nYour order is on the way.\nOur delivery person will call you on arrival";
                return $cheaps->customer_order_processing_status($message, $connection, false);
            }
            else if ($session_data[$cursor] == CheapsHandler::$CANCEL_ORDER) {
                $message = "Order Cancelled.\nOrder could not be processed.\nCome Again soon. :)";
                $cheaps->clear_customer_session($session_id);
                return $cheaps->customer_order_processing_status($message, $connection, false);
            }
            else
            {
                CheapsHandler::process_customer_name($session_data[$cursor], $session_id);
                $connection['message_type'] = true;
                return $cheaps->handleUSSDresponse($connection, $this->officeList("")["data"]);
            }
        }

        if ($size == 7) {
            Redis::hset($session_id, "delivery_location", $this->officeList("")
            ["location"][$session_data[$cursor] - 1]);
            //check if invalid selection is made.
            return $cheaps->complete_customer_order($session_id, $connection);
        }

        if ($size == 8) {
            // make call to mobile money payment
            //wait for response and then send this reply back to user
            $order = Redis::hgetall($session_id);
            if ($session_data[$cursor] == CheapsHandler::$CONFIRM_ORDER) {
                // make call to mobile money payment
                //wait for response and then send this reply back to user
                $order = Redis::hgetall($session_id);
                dispatch(new OrderJob($order, $session_id, $this->generateUuid()))->delay(2); // dispatch order for
                //existing customer
                $message = "Order processed\nYour order is on the way.\nOur delivery person will call you on arrival";
                return $cheaps->customer_order_processing_status($message, $connection, false);
            }
            else if ($session_data[$cursor] == CheapsHandler::$CANCEL_ORDER) {
                $message = "Order Cancelled.\nOrder could not be processed.\nCome Again soon. :)";
                $cheaps->clear_customer_session($session_id);
                return $cheaps->customer_order_processing_status($message, $connection, false);
            }
        }

    }




    public function customer_not_registered ($session_data, $connection, $cheaps, $session_id) {
        $size = count($session_data);
        $cursor = $size - 1;
        Log::info("Size " . $size);
        if ($size == 5) {
            CheapsHandler::process_customer_name($session_data[$cursor], $session_id);
            $connection['message_type'] = true;
            return $cheaps->handleUSSDresponse($connection, $this->officeList("")["data"]);
        }



        if ($size == 6) {
            //confirm
            Redis::hset($session_id, "delivery_location", $this->officeList("")
            ["location"][$session_data[$cursor] - 1]);
            //check if invalid selection is made.
            return $cheaps->complete_customer_order($session_id, $connection);
        }

        if ($size == 7 && $session_data[$cursor] == CheapsHandler::$CONFIRM_ORDER) {
            // make call to mobile money payment
            //wait for response and then send this reply back to user
            $order = Redis::hgetall($session_id);
            dispatch(new OrderJob($order, $session_id, $this->generateUuid()))->delay(2);
            $message = "Order processed\nYour order is on the way.\nOur delivery person will call you on arrival";
            return $cheaps->customer_order_processing_status($message, $connection, false);
        }

        if ($size == 7 && $session_data[$cursor] == CheapsHandler::$CANCEL_ORDER) {
            $message = "Order Cancelled.\nOrder could not be processed.\nCome Again soon. :)";
            $cheaps->clear_customer_session($session_id);
            return $cheaps->customer_order_processing_status($message, $connection, false);
        }
    }


}
