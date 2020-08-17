<?php


namespace App;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class CheapsHandler
{


    public function validate_customer($phone_number, $message_type)
    {
        return ($message_type) ? Customer::where('phone_number', $phone_number)->where('delete_status', 'NOT DELETED')
            ->first() : null;
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
            Log::info("Has session id");
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

    public function format_user_input(string $user_input)
    {
        return ucfirst(strtolower($user_input));
    }
}
