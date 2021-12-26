<?php
namespace Api\V1\Controllers;
use Api\V1\Models\Listing;
use Api\V1\Models\User;
use Api\V1\Models\Transaction;
use Laminas\Diactoros\ServerRequest;
use Api\V1\Controllers\AuthController;
use Laminas\Diactoros\Response\JsonResponse;
use Core\DB;

class PaymentController
{

    /**
     * Payment Link for listings.
     *
     * The function gets the listing information and sends a post request to flutterwave with the listing information as meta alongside other important information (e.g price, currency, etc)
     * If the request goes through, flutterwave responds with a payment link for the user to use.
     * 
     * @param int $listing_id
     * @return object 
     */

    public function pay($listing_id)
    {
        $listing = new Listing();
        $listing = json_decode($listing->getListing($listing_id)->getBody()->getContents());//get Listing Information
        $user_id = AuthController::getUserId(); //logged in user id
        if(!(is_null($listing->currency)) && !(empty($listing->currency))) 
        {
            $currency = $listing->currency;
        } else
        {
            $currency = "NGN";
        }

        $user = new User();
        $user =  json_decode($user->getUser($user_id)->getBody()->getContents());//get Logged in user information

        $transaction_ref = md5(rand());//create random transaction reference
        $data = [
            "tx_ref" => $transaction_ref,
            "amount" => $listing->price,
            "currency" => $currency,
            "redirect_url" => "http://localhost:8000/pay_redirect",
            "payment_options" => "card",
            "meta" => [
               "occupant_id" => $user_id,
               "listing_id" => $listing_id
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


        //Sends a post request to flutterwave API in order to get payment link for users
        $url = "https://api.flutterwave.com/v3/payments";

        //creat curl session
        $curl = curl_init($url);
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

    /**
     * Redirect link for flutterwave.
     *
     * After someone attempts to pay, paystack redirects them to this link alongside sending some data in the url. 
     * The function then sends a post request to flutterwave to confirm the transaction status, saves record to the databse and gives appropriate response to users. 
     * 
     * @param array $request
     * @return object 
     */

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
    //   return $response;
        if(json_decode($response)->status == "success")
        {
            $user_id = json_decode($response)->data->meta->occupant_id;

            //save transaction details to database;
            $transaction = new Transaction();
            $transaction->createTransaction(json_decode($response));
            
            $data = ["occupant_id" => $user_id, "is_available" => 0];
           
            //update listing 
            $statement = DB::$con->prepare("UPDATE `listings` SET `occupant_id`=$user_id,`is_available`=0 WHERE id=:id");
            $statement->bindParam(":id", json_decode($response)->data->meta->listing_id);
            
            if($statement->execute())
            {
                return new JsonResponse(["message" => "Payment Successful"], 200);  
            }
            
        }else
        {
             //save transaction details to database;
             $transaction = new Transaction();
             $transaction->createTransaction(json_decode($response));

            return new JsonResponse(["error" => "Something went wrong. Please try again."], 500);
        }
    }

}