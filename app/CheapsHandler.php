<?php


namespace App;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CheapsHandler
{


    public function validate_customer($phone_number, $message_type)
    {
        return ($message_type) ? Customer::where('phone_number', $phone_number)->where('delete_status', 'NOT DELETED')
            ->first() : null;
    }



    public  function handleUSSDresponse ($USER_ID, $customer_phone_number, $cheaps_message, $message_type)
    {
        return json_encode(['USERID' => $USER_ID, 'MSISDN' => $customer_phone_number, 'MSG' => $cheaps_message,
            'MSGTYPE' => $message_type]);
    }

    public function manage_customer_session($session_id)
    {
        $session_request = [];

        if (Session::has($session_id))
        {
            //retrieve session data for processing
            $session_request = Session::get($session_id);
            return $session_request;
        }
        else
        {
            //create session
            Session::put($session_id, json_encode([]));
            return $session_request;
        }
    }


}
