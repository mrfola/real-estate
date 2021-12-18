<?php
namespace API\V1\Models;
use Core\DB;
use PDO;
use Laminas\Diactoros\Response\JsonResponse;

class User
{
    protected $allowedFields = ["name", "email", "password", "phonenumber"];

    public function createUser($data)
    {
        //IMPROVEMENTS: add salt to password

        //check if email is unique
        $no_of_email = DB::numOfRows('email', 'users', $data['email']);
        if($no_of_email >= 1)
        {
            return new JsonResponse(["error" => ['email' => 'Your email has already been used']], 400);
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $queryParams = DB::getQueryParams($data, $this->allowedFields);

        $statement = DB::$con->prepare("INSERT INTO `users` (`name`, `email`, `password` , `phonenumber`) VALUES (:name, :email, :password, :phonenumber)");
        DB::bindAllParams($statement, $data, $this->allowedFields);
        $statement->execute();

        return $this->getUser(DB::$con->lastInsertId());
    }

    public function getUser($id)
    {
        $statement = DB::$con->prepare("SELECT * FROM `users` WHERE id=:id");//$id
        $statement->bindParam(":id", $id);
        $statement->execute();
        return new JsonResponse($statement->fetch(PDO::FETCH_ASSOC), 200);
    }
}