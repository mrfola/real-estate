<?php
namespace API\V1\Controllers;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response\JsonResponse;
use Core\DB;
use PDO;
use Valitron\Validator;
use Exception;

class UserController
{
    protected $db, $con;

    public function __construct()
    {
        $this->db = new DB();
        $this->con = $this->db->connect("real-estate", "root", "");

    }

    public function show($id)
    {
        $statement = $this->con->prepare("SELECT * FROM `users` WHERE id=:id");//$id
        $statement->bindParam(":id", $id);
        $statement->execute();

        return new JsonResponse($statement->fetch(PDO::FETCH_ASSOC), 200);
    }

    public function create(ServerRequest $request)
    {
        //IMPROVEMENTS: 
        //add salt to database field

        //get array data from request
        $data = $request->getParsedBody();

        //check if email is unique
        $statement = $this->con->prepare("SELECT COUNT(*) FROM `users` WHERE email=:email");
        $statement->bindValue(":email", $data['email']);
        $statement->execute();
        $email_count = $statement->fetchColumn();

        if($email_count > 0)
        {
            return new JsonResponse(["error" => ['email' => 'Your email has already been used']]);
        }

        //Validate
        $validate = new Validator($data);
        $validate->rule('required', ['name', 'email', 'password']);
        $validate->rule('email', 'email');

        if ($validate->validate())
        {   
            $allowedFields = ["name", "email", "password"];
            // $options = [
            //     'salt' => custom_function_for_salt,
            // ];
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    
            $queryParams = $this->db->getQueryParams($data, $allowedFields);
    
            $statement = $this->con->prepare("INSERT INTO `users` (`name`, `email`, `password`) VALUES (:name, :email, :password)");
            $this->db->bindAllParams($statement, $data, $allowedFields);
            $statement->execute();
            
            return $this->show($this->con->lastInsertId());
        }
        else
        {   
          return new JsonResponse(["errors" => $validate->errors()]);  
        }
    }
}