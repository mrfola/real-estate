<?php
namespace API\V1\Controllers;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Core\DB;
use PDO;
use Firebase\JWT\Key;
use Firebase\JWT\JWT;



class ListingController
{ 
    
    private $con, $db, $key, $auth;

    public function __construct()
    {
        $this->db = new DB();
        $this->con = $this->db->connect("real-estate", "root", "");
        $this->auth = new AuthController();
    }

    public function index(ServerRequest $request)
    {
        if($this->auth->validateToken($request))
        {
              //IMPROVEMENTS: 
            //Make response HATEOS compliant
            $statement = $this->con->prepare("SELECT * FROM `listings`");
            $statement->execute();

            return new JsonResponse($statement->fetchAll(PDO::FETCH_ASSOC), 200);
        }else
        {
            return new JsonResponse(["errors" => "Couldn't verify your token"], 400);
        }

      
    }

    public function show($id)
    {
        $statement = $this->con->prepare("SELECT * FROM `listings` WHERE id=:id");
        $statement->bindParam(":id", $id);
        $statement->execute();

        return new JsonResponse($statement->fetch(PDO::FETCH_ASSOC), 200);
    }

    public function create(ServerRequest $request)
    {
        //IMPROVEMENTS:
        //Request should be validated before inserting (compulsory fields should be enforced)
        //Add $allowedFields variable for validation

        //get request content
        $data = $request->getParsedBody();
        try 
        {
            $statement = $this->con->prepare("INSERT INTO `listings` (`name`, `description`, `pictures`, `details`, `location`) 
            VALUES (:name, :description, :pictures, :details, :location)"); 

            $statement->bindParam(":name", $data['name']);
            $statement->bindParam(":description", $data['description']);
            $statement->bindParam(":pictures", $data['pictures']);
            $statement->bindParam(":details", $data['details']);
            $statement->bindParam(":location", $data['location']);

            $statement->execute();

            $id = $this->con->lastInsertId();

        } catch (Exception $e)
        {
            return json_encode(["error" => $e]);
        }
      
       return $this->show($id);
    }

    public function update(ServerRequest $request, $id)
    {
        //IMPROVEMENTS: 
        //You should only be able to update your own listing
        //Add user id to `listing` table
        //reject empty request

        $data =  $request->getQueryParams();
        $allowedFields = [ "name", "description", "pictures", "details","California","is_available"];

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
         //IMPROVEMENTS: 
        //You should only be able to delete your own listing

        $statement = $this->con->prepare("DELETE FROM `listings` WHERE id=:id");
        $statement->bindParam(":id", $id);
        $statement->execute();

        return new JsonResponse(["id" => $id, "message" => "Your post has been deleted"], 200);
    }


}