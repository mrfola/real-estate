<?php
namespace API\V1\Models;
use Core\DB;
use PDO;
use Laminas\Diactoros\Response\JsonResponse;
use API\V1\Exceptions\UsedEmailException;

class User
{
    protected $allowedFields = ["name", "email", "password", "phonenumber", "account_number", "bank"];

    /**
     * Stores new user to DB
     *  
     * @param array $data
     * @return object 
     * 
     */
    public function createUser($data)
    {
        //IMPROVEMENTS: add salt to password

        //check if email is unique
        $no_of_email = DB::numOfRows('email', 'users', $data['email']);
        if($no_of_email >= 1)
        {
            throw new UsedEmailException("Your email has been used");
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $queryParams = DB::getQueryParams($data, $this->allowedFields);

        $statement = DB::$con->prepare("INSERT INTO `users` (`name`, `email`, `password` , `phonenumber`) VALUES (:name, :email, :password, :phonenumber)");
        DB::bindAllParams($statement, $data, $this->allowedFields);
        $statement->execute();

        return $this->getUser(DB::$con->lastInsertId());
    }

    /**
     * Gets single user from database
     *  
     * @param int $id
     * @return object 
     * 
     */
    public function getUser($id)
    {
        $statement = DB::$con->prepare("SELECT * FROM `users` WHERE id=:id");
        $statement->bindParam(":id", $id);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);
        unset($user["password"]);//remove password field
        return new JsonResponse($user, 200);
    }
}