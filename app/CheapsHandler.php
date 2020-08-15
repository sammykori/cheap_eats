<?php


namespace App;


use Illuminate\Support\Facades\Log;

class CheapsHandler
{


    public function validate_customer($phone_number, $message_type)
    {
        return ($message_type) ? Customer::where('phone_number', $phone_number)->where('delete_status', 'NOT DELETED')
            ->first() : null;
    }



    public static function handleUSSDresponse ($USER_ID, $customer_phone_number, $cheaps_message, $message_type)
    {
        Log::info("response handler called");
//        header('Content-type: application/json');
        $res = [
            'USERID' => $USER_ID,
            'MSISDN' => $customer_phone_number,
            'MSG' => $cheaps_message,
            'MSGTYPE' => $message_type
        ];
        return json_encode($res);
    }

}
