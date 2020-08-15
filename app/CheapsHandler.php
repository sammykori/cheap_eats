<?php


namespace App;


class CheapsHandler
{


    public function validate_customer($phone_number, $message_type)
    {
        return ($message_type) ? Customer::where('phone_number', $phone_number)->where('delete_status', 'NOT DELETED')
            ->first() : null;
    }



    public function handleUSSDresponse ($customer_phone_number, $cheaps_message, $message_type)
    {
        header('Content-type: Application/json');
        return json_encode(['USERID' => env('CHEAPSUSERID'), 'MSISDN' => $customer_phone_number, 'MSG' => $cheaps_message,
            'MSGTYPE' => $message_type], '');
    }

}
