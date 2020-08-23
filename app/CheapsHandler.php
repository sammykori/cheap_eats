<?php


namespace App;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class CheapsHandler
{

    public static $CONFIRM_ORDER = 1;
    public static $CANCEL_ORDER = 2;

    public function validate_customer($phone_number, $message_type = false)
    {
        $session_id = base64_encode($phone_number);
        $customer_profile = Redis::hget($session_id, "customer_profile");
        if ($customer_profile == null) {
            Log::info("came in here");
            $customer_data = Customer::where('phone_number', $phone_number)->where('delete_status', 'NOT DELETED')
                ->select('customer_id', 'customer_first_name', 'customer_last_name', 'office_location')->first();
            if ($customer_data != null) {
                Redis::hset($session_id, "customer_profile", json_encode($customer_data));
            }
        }
        Log::info("Found " . $customer_profile);
        return $customer_profile;
    }


    public  function handleUSSDresponse ($connection, $cheaps_message)
    {
        return json_encode(['USERID' => $connection['user_id'],
            'MSISDN' => $connection['phone_number'], 'MSG' => $cheaps_message,
            'MSGTYPE' => $connection['message_type']]);
    }

    public function manage_customer_session($session_id)
    {
        $session_request = [];
        if (Redis::exists($session_id))
        {
            //retrieve session data for processing
            $session_request = Redis::lrange($session_id, 0, -1);
            return $session_request;
        }
        else
        {
            //create session
            Redis::rpush($session_id, "");
            $session_request = Redis::lrange($session_id, 0, -1);
            return $session_request;
        }
    }

    public static function format_user_input(string $user_input)
    {
        return ucfirst(strtolower($user_input));
    }


    public function clear_customer_session($session_id) {
        Redis::del('select:'.$session_id);
        Redis::del($session_id);
    }

    public function complete_customer_order ($session_id, $connection) {
        $order_data = Redis::hgetall($session_id);
        if (Redis::hexists($session_id, "receiver_name")) {
            $connection["disp_name"] = $order_data['receiver_name'];
        }
        $message = $connection["disp_name"];
        $message .=  " (".'0'.substr($connection['phone_number'], 3) .")\n";
        $message .= $order_data["food_name"]." (".$order_data["quantity"].")\n";
        $message .= "1. Confirm\n2. Cancel";
        $connection['message_type'] = true;
        return $this->handleUSSDresponse($connection, $message);
    }

    public function customer_order_processing_status ($message, $connection, $ismessage_type) {
//        Log::info(json_encode($connection));
        $connection['message_type'] = $ismessage_type;
        return $this->handleUSSDresponse($connection, $message);
    }


    public static function process_customer_name ($customer_name, $session_id) {
        $name_parts = explode(" ", $customer_name);
        if (count($name_parts) == 1) {
            $name_parts = implode(" ", [CheapsHandler::format_user_input($name_parts[0]),
                CheapsHandler::format_user_input($name_parts[0])]);
        } else if (count($name_parts) > 1) {
            $name_parts = implode(" ", [CheapsHandler::format_user_input($name_parts[0]),
                CheapsHandler::format_user_input($name_parts[1])]);
        }
        Redis::hset($session_id, 'receiver_name', $name_parts);
    }


    public static  function validate_user_input($user_input, $cheap_input, $expected_input_type) {

         if (strcmp(gettype($user_input), $expected_input_type) != 0) {
             return (new self())->custom_validate($cheap_input);
         } else {
             if (strcmp(gettype($user_input), 'int') == 0 && !Arr::exists(array_values($cheap_input['options']), $user_input)) {
                 return (new self())->custom_validate($cheap_input);
             }
         }
    }

    public  function custom_validate($cheap_input) {
        $this->clear_customer_session($cheap_input['session_id']);
        return $this->customer_order_processing_status("Sorry cheaps detected an invalid input\n Try again :)",
            $cheap_input['connection'], false);
    }
}
