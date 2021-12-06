<?php

namespace API\V1\Controllers;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response\JsonResponse;
use Core\DB;
use PDO;
use Valitron\Validator;
use Exception;
use Firebase\JWT\JWT;


class AuthController
{
    private $db, $con;

    public function __construct()
    {
        $this->db = new DB();
        $this->con = $this->db->connect("real-estate", "root", "");
    }

    public function getToken()
    {
        $iat = time();
        $exp = $iat + 60*60;
        $key = "ITuCY0SgavWs5MIqf5642Fk0hnW8JkoKifNM8XclZtLXKqPlWkfRgOBGaQm3mJyT3m8lOfkqu0wR29tq4Yt1uH7xgP9Ru7JUu4zn";

        $payload = array(
            "iss" => "127.0.0.1:8000", //issuer
            "aud" => "http://postman.com", //audience
            "iat" => $iat, //token issuance time
            "nbf" => $exp //token expiry time.
        );

        $jwt = JWT::encode($payload, $key, 'HS256');

        return new JsonResponse(["token" => $jwt, "expires" => $exp]);
    }

    public function login(ServerRequest $request)
    {
        $data = $request->getParsedBody();
        $validator = new Validator($data);
        $validator->rule('required', ['email', 'password']);
        $validator->rule('email', 'email');

        
        if($validator->validate())
        {
            $allowedFields = ["email", "password"];

            $is_login = true;

            //get hash for login email from
            $statement = $this->con->prepare("SELECT `password` FROM `users` WHERE email=:email");
            $statement->bindValue(":email", $data['email']);
            $statement->execute();
            $hash = $statement->fetch(PDO::FETCH_ASSOC);

            if($hash)
            {
                if(password_verify($data['password'], $hash["password"]))
                {
                    $is_login = true;

                }else
                {
                    $is_login = false;
                }

            }else
            {
                $is_login = false;
            }

            if($is_login == true)
            {
                //generate token
                return $this->getToken();

            }else
            {
                //error message
                $error = "Wrong username or password";
                return new JsonResponse(["errors" => $error], 504);

            }


            // 

            // $statement = $this->con->prepare("SELECT COUNT(*) FROM `users` WHERE email=:email, password=:password");
            // $statement = $this->db->bindAllParams($statement, $data, $allowedFields);
        }else
        {
            return new JsonResponse(["errors" => $validator->errors()], 504);
        }
    }

    public function logout()
    {
        
    }
}