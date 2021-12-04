<?php
namespace API\V1\Controllers;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Core\DB;
use PDO;

class ListingController
{ 
    
    private $con, $db;

    public function __construct()
    {
        $this->db = new DB();
        $this->con = $this->db->connect("real-estate", "root", "");
    }

    public function index()
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

        return new JsonResponse($statement->fetch(PDO::FETCH_ASSOC), 200);
    }

    public function create(ServerRequest $request)
    {
        //IMPROVEMENTS:
        //Request should be validated before inserting (compulsory fields should be enforced)

        //$queryParameters = $request->getQueryParams();

        //get request content
        $body = $request->getParsedBody();
        try 
        {
            $statement = $this->con->prepare("INSERT INTO `listings` (`name`, `description`, `pictures`, `details`, `location`) 
            VALUES (:name, :description, :pictures, :details, :location)"); 

            $statement->bindParam(":name", $body['name']);
            $statement->bindParam(":description", $body['description']);
            $statement->bindParam(":pictures", $body['pictures']);
            $statement->bindParam(":details", $body['details']);
            $statement->bindParam(":location", $body['location']);

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

        $body =  $request->getQueryParams();

        //get query parameter
        $queryParams = $this->db->getQueryParams($body, $allowedFields = [ "name", "description", "pictures", "details","California","is_available"]);

        $statement = $this->con->prepare("UPDATE `listings` SET $queryParams WHERE id=:id");
        $this->db->bindAllParams($statement, $body);
        $statement->bindParam(":id", $id);

        $statement->execute();

        return $this->show($id);
    }

    public function destroy()
    {
        return json_encode(["data" => "Listing deleted"]);
    }


}