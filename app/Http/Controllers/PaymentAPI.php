<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;


trait PaymentAPI{

    public function requestToPay($uuid, $amount, $phone){
        $response = Http::withHeaders(array(
            'X-Reference-Id' => $uuid,
            'X-Target-Environment' => 'sandbox',
            'Ocp-Apim-Subscription-Key' => 'de88937e791c4abbb2cd6484a075a10a',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSMjU2In0.eyJjbGllbnRJZCI6IjZiMTE5OWM5LWUwZTQtNDYxOC05NjljLTVlODk3Y2E0NTk0YSIsImV4cGlyZXMiOiIyMDIwLTA4LTEyVDE1OjM1OjA4LjU1MSIsInNlc3Npb25JZCI6ImZhY2RiN2Y1LTM2YjAtNDM5ZS05YjgwLTRlODg4ZjViYmQ3OCJ9.iHZMzboknaJ0cQKPxkBuUh0UsHDOW5vX0nGn9Qnn8NTQTHZvm30Fss8kNDMB5G5Uf_9g8O279gJdCtE2Tvorm3KZoz3nOAlEXUz3QpNL0jlST9WIodcIwxkFSrM9OXheIbkWjA9nJSebfFqiB34TJrTh5VL2O--CxOp3D-IOuwiehbiKSQjQ6FsGf5cHBwEnXO3qzqI3xzbayRq4eD5FrDYjqSbVd-V_tzsM7IDWnp1lqjmGK0kmQhy1l9kMTQlQKzXhmMZuQNZDNktIcm6t8LS3LvZt4jiKc1t1RoGf-P5Rzj9vcwzjZ2r-GLbH2cNFhkpvc5mSgRwYCMpjeNkLow',
            'Content-Type' => 'application/json'
            ))
            ->post('https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay',
            [
                'amount' => $amount,
                'currency' => 'EUR',
                'externalId' => $uuid,
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => $phone
                ],
                'payerMessage' => 'Pay for food at Cheap Eats',
                'payeeNote' => 'Food Payment',
            ]);

        return $response->status();
    }

    public function requestPayStatus($uuid){
        $response = Http::withHeaders(array(
            'X-Target-Environment' => 'sandbox',
            'Ocp-Apim-Subscription-Key' => 'de88937e791c4abbb2cd6484a075a10a',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSMjU2In0.eyJjbGllbnRJZCI6IjZiMTE5OWM5LWUwZTQtNDYxOC05NjljLTVlODk3Y2E0NTk0YSIsImV4cGlyZXMiOiIyMDIwLTA4LTEyVDE1OjM1OjA4LjU1MSIsInNlc3Npb25JZCI6ImZhY2RiN2Y1LTM2YjAtNDM5ZS05YjgwLTRlODg4ZjViYmQ3OCJ9.iHZMzboknaJ0cQKPxkBuUh0UsHDOW5vX0nGn9Qnn8NTQTHZvm30Fss8kNDMB5G5Uf_9g8O279gJdCtE2Tvorm3KZoz3nOAlEXUz3QpNL0jlST9WIodcIwxkFSrM9OXheIbkWjA9nJSebfFqiB34TJrTh5VL2O--CxOp3D-IOuwiehbiKSQjQ6FsGf5cHBwEnXO3qzqI3xzbayRq4eD5FrDYjqSbVd-V_tzsM7IDWnp1lqjmGK0kmQhy1l9kMTQlQKzXhmMZuQNZDNktIcm6t8LS3LvZt4jiKc1t1RoGf-P5Rzj9vcwzjZ2r-GLbH2cNFhkpvc5mSgRwYCMpjeNkLow',
            'Content-Type' => 'application/json'
            ))
            ->get("https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay/$uuid");
        
        return $response;
    }

}
