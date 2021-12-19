<?php
namespace  API\V1\Models;
use API\V1\Controllers\AuthController;
use API\V1\Models\Listing;
use Core\DB;
class Transaction
{
    protected $allowedFields = ["user_id", "email", "listing_id", "status", "amount", "currency", "transaction_id", "first_six_digits", "last_four_digits", "created_at"];
   
    
    /**
     * Stores Transaction Record in database
     * 
     * @param array $data
     * @return object 
     * 
     */

    public function createTransaction($data)
    {   
        $user_id = $data->data->meta->occupant_id;
        $email = $data->data->customer->email;
        $listing_id = $data->data->meta->listing_id;
        $status = $data->status;
        $amount = $data->data->amount;
        $currency =  $data->data->currency;
        $transaction_id =  $data->data->tx_ref;
        $first_six_digits = $data->data->card->first_6digits;
        $last_four_digits =  $data->data->card->last_4digits;
        $created_at =  $data->data->created_at;
      
        $new_data = ["user_id" => $user_id, "email" => $email, "listing_id" => $listing_id, "status" => $status, "amount" => $amount, "currency" => $currency,
        "transaction_id" => $transaction_id, "first_six_digits" => $first_six_digits, "last_four_digits" => $last_four_digits, "created_at" => $created_at];
                    

        try 
        { 
            //transaction record
            $statement = DB::$con->prepare("INSERT INTO `transactions` 
            (`user_id`, `email`, `listing_id`, `status`, `amount`, `currency`, `transaction_id`, `first_six_digits`, `last_four_digits`, `created_at`) 
            VALUES (:user_id, :email, :listing_id, :status, :amount, :currency, :transaction_id, :first_six_digits, :last_four_digits, :created_at)"); 

            DB::bindAllParams($statement, $new_data, $this->allowedFields);

            $statement->execute();

        } catch (Exception $e)
        {
            return json_encode(["error" => $e]);
        }
        

    }

}