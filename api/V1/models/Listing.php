<?php
namespace API\V1\Models;

use Core\DB;
use PDO;
use Laminas\Diactoros\Response\JsonResponse;
use API\V1\Controllers\AuthController;
use API\V1\Exceptions\EmptyQueryException;
use API\V1\Exceptions\NotFoundException;
use API\V1\Exceptions\UnauthorizedUserException;


Class Listing
{
   protected $allowedFields = ["owner_id", "name", "description", "currency", "price", "images", "details", "location"];

    
    /**
     * Get All Listings.
     * 
     * @return object 
     * 
     */

    public function getAllListings()
    {
        //IMPROVEMENTS: 
        //Make response HATEOS compliant
        $statement = DB::$con->prepare("SELECT * FROM `listings`");
        $statement->execute();
        return new JsonResponse($statement->fetchAll(PDO::FETCH_ASSOC), 200);
    }

    
    /**
     * Get Single Listing.
     * 
     * @param array $id
     * @return object 
     * 
     */

    public function getListing($id)
    {
        $statement = DB::$con->prepare("SELECT * FROM `listings` WHERE id=:id");
        $statement->bindParam(":id", $id);
        $statement->execute();

        if($statement->rowCount() <= 0)
        {
            return new JsonResponse(["error" => "Listing not found"], 404);
        }

        return new JsonResponse($statement->fetch(PDO::FETCH_ASSOC), 200);
    }

    
    /**
     * Create Listing.
     * 
     * @param array $data
     * @return object 
     * 
     */

    public function createListing($data)
    {
        try 
        {
            $statement = DB::$con->prepare("INSERT INTO `listings` (`owner_id`, `name`, `description`, `images`, `details`, `location`) 
            VALUES (:owner_id, :name, :description, :images, :details, :location)"); 

            DB::bindAllParams($statement, $data, $this->allowedFields);

            $statement->execute();

            return $this->getListing(DB::$con->lastInsertId());


        } catch (Exception $e)
        {
            return json_encode(["error" => $e]);
        }
        
    }

    /**
     * Update Listing.
     * 
     * @param array $data
     * @param int $id
     * @return object 
     * 
     */

    public function updateListing($data)
    {
        $id = $data["id"];
        unset($data["id"]);

         //reject empty request
         if (is_null($data) || empty($data))
         {
            throw new EmptyQueryException("No value to update");
         }
 
         //Check if listing exists
         $num_of_listings = DB::numOfRows('id', 'listings', $id);
         if($num_of_listings <= 0)
         {
            throw new NotFoundException("Listing Not Found");
         }
 
         //only owner of listing can update
         $listing = $this->getListing($id);                  
         $owner_id = json_decode($listing->getBody()->getContents())->owner_id;

         if($owner_id != AuthController::getUserId())
         { 
            throw new UnauthorizedUserException("You are not authorized to carry out this operation");
         }

        //remove "owner_id" from request if present - you are not permitted to change the owner of a listing
         if(in_array("owner_id", $data))
         {
            unset($data["owner_id"]);
         }
 
         //get query parameter
         $queryParams = DB::getQueryParams($data, $this->allowedFields);

         $statement = DB::$con->prepare("UPDATE `listings` SET $queryParams WHERE id=:id");
         DB::bindAllParams($statement, $data, $this->allowedFields);
         $statement->bindParam(":id", $id);
 
         $statement->execute();
 
         return $this->getListing($id);
    }

    /**
     * Delete Listing.
     * 
     * @param int $id
     * @return object 
     * 
     */

    public function deleteListing($id)
    {
         //Check if listing exists
         $num_of_listings = DB::numOfRows('id', 'listings', $id);
         if($num_of_listings <= 0)
         {
             throw new NotFoundException("Listing does not exist");
         }
 
         //only owner of listing can update
         $listing = $this->getListing($id);         
         $owner_id = json_decode($listing->getBody()->getContents())->owner_id;
         if($owner_id != AuthController::getUserId())
         {
             throw new UnauthorizedUserException("You are not authorized to carry out this operation");
         }
 
         $statement = DB::$con->prepare("DELETE FROM `listings` WHERE id=:id");
         $statement->bindParam(":id", $id);
         $statement->execute();
 
         return new JsonResponse(["id" => $id, "message" => "Your listing has been deleted"], 200);
    }
}