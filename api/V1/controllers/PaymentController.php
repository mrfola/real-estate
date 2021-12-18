<?php
namespace API\V1\Controllers;
use API\V1\Models\Listing;
use API\V1\Models\User;
use Laminas\Diactoros\ServerRequest;


class PaymentController
{
    public function pay($id)
    {
        $listing = new Listing();
        $listing = json_decode($listing->getListing($id)->getBody()->getContents());
        $user_id = $listing->user_id;
        if(!(is_null($listing->currency)) && !(empty($listing->currency))) 
        {
            $currency = $listing->currency;
        } else
        {
            $currency = "NGN";
        }

        $user = new User();
        $user =  json_decode($user->getUser($user_id)->getBody()->getContents());

        $transaction_ref = md5(rand());//create random transaction reference
        $data = [
            "tx_ref" => $transaction_ref,
            "amount" => $listing->price,
            "currency" => $currency,
            "redirect_url" => "http://localhost:8000/pay_redirect",
            "payment_options" => "card",
            "meta" => [
               "consumer_id" => $user_id,
            ],
            "customer" => [
               "email" => $user->email,
               "phonenumber" => $user->phonenumber,
               "name" => $user->name
            ],
            "customizations"=>[
               "title" => "Payment for {$listing->name}",
            ]
        ];

        $url = "https://api.flutterwave.com/v3/payments";

        //creat curl session
        $curl = curl_init($url);
        //curl_setopt($curl, CURLOPT_URL, 'https://www.edureka.co');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER , true);
        curl_setopt($curl, CURLOPT_POST, "post");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        //set curl headers
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer ".$_ENV["FLUTTERWAVE_SECRET_KEY"],
            "Content-Type: application/json"
        ]);

        //execute curl request with all previous settings
        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl); //close curl session

        if($error)
        {
            return "An error occured:" . $error;
        }else
        {
            return $response. PHP_EOL;
        }
    }

    public function pay_redirect(ServerRequest $request)
    {
        $transaction_id = $request->getQueryParams()["transaction_id"];

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.flutterwave.com/v3/transactions/{$transaction_id}/verify",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
             "Authorization: Bearer ".$_ENV["FLUTTERWAVE_SECRET_KEY"],
            "Content-Type: application/json"
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;

        if($response["status"] == "success")
        {
            //UPDATE:
            //save transaction details to database;

            return new JsonResponse(["message" => "Payment Successful"], 200);
        }else
        {
            return new JsonResponse(["error" => "Something went wrong. Please try again."], 500);
        }
    }

}