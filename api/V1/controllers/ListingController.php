<?php
namespace API\V1\Controllers;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Core\DB;
use PDO;
use Firebase\JWT\Key;
use Firebase\JWT\JWT;
use Valitron\Validator;
use API\V1\Controllers\AuthController;


class ListingController
{ 
    
    private $con, $db, $key, $auth_user_id;

    public function __construct()
    {
        $this->db = new DB();
        $this->con = $this->db->connect("real-estate", "root", "");
        $this->auth_user_id = AuthController::get_user();
    }

    public function index(ServerRequest $request)
    {
            //IMPROVEMENTS: 
            //Make response HATEOS compliant
            $statement = $this->con->prepare("SELECT * FROM `listings`");
            $statement->execute();

            return new JsonResponse($statement->fetchAll(PDO::FETCH_ASSOC), 200);

      
    }

    public function show($id)
    {
        $statement = $this->con->prepare("SELECT * FROM `listings` WHERE id=:id");
        $statement->bindParam(":id", $id);
        $statement->execute();

        if($statement->rowCount() <= 0)
        {
            return new JsonResponse(["error" => "Listing not found"], 404);
        }
        return new JsonResponse($statement->fetch(PDO::FETCH_ASSOC), 200);
    }

    public function create(ServerRequest $request)
    {
        //get request content
        $data = $request->getParsedBody();
        $data["user_id"] = AuthController::get_user();

        
        $allowedFields = ["user_id", "name", "description", "pictures", "details", "location"];
        
        //validate
        $validator = new Validator($data);
        $validator->rule('required',  $allowedFields);

        if($validator->validate())
        {
            try 
            {
                $statement = $this->con->prepare("INSERT INTO `listings` (`user_id`, `name`, `description`, `pictures`, `details`, `location`) 
                VALUES (:user_id, :name, :description, :pictures, :details, :location)"); 

                $this->db->bindAllParams($statement, $data, $allowedFields);
    
                $statement->execute();
    
                $id = $this->con->lastInsertId();
    
            } catch (Exception $e)
            {
                return json_encode(["error" => $e]);
            }
          
           return $this->show($id);

        } else
        {
            return new JsonResponse(["errors" => $validator->errors()], 504);
        }

    }

    public function update(ServerRequest $request, $id)
    {

        //reject empty request
        $data =  $request->getQueryParams();
        if (is_null($data) || empty($data))
        {
            return new JsonResponse(["error" => "No value to update"], 401);
        }

        //Check if listing exists
        $listing = $this->show($id);
        if($listing->getStatusCode() == 404)
        {
            return new JsonResponse(["error" => "Listing does not exist"], 404);
        }

        //You should only be able to update your own listing
        $user_id = json_decode($listing->getBody()->getContents())->user_id;
        if($user_id != $this->auth_user_id)
        {
            return new JsonResponse(["error" => "You are not authorized to carry out this operation"], 401);
        }

        $allowedFields = ["name", "description", "pictures", "details","California", "is_available"];

        //get query parameter
        $queryParams = $this->db->getQueryParams($data, $allowedFields);

        $statement = $this->con->prepare("UPDATE `listings` SET $queryParams WHERE id=:id");
        $this->db->bindAllParams($statement, $data, $allowedFields);
        $statement->bindParam(":id", $id);

        $statement->execute();

        return $this->show($id);
    }

    public function destroy($id)
    {
        //Check if listing exists
        $listing = $this->show($id);
        if($listing->getStatusCode() == 404)
        {
            return new JsonResponse(["error" => "Listing does not exist"], 404);
        }

        //You should only be able to update your own listing
        $user_id = json_decode($listing->getBody()->getContents())->user_id;
        if($user_id != $this->auth_user_id)
        {
            return new JsonResponse(["error" => "You are not authorized to carry out this operation"], 401);
        }

        $statement = $this->con->prepare("DELETE FROM `listings` WHERE id=:id");
        $statement->bindParam(":id", $id);
        $statement->execute();

        return new JsonResponse(["id" => $id, "message" => "Your post has been deleted"], 200);
    }


}