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
    private $db, $con, $key;

    public function __construct()
    {
        $this->db = new DB();
        $this->con = $this->db->connect("real-estate", "root", "");
        $this->key = "ITuCY0SgavWs5MIqf5642Fk0hnW8JkoKifNM8XclZtLXKqPlWkfRgOBGaQm3mJyT3m8lOfkqu0wR29tq4Yt1uH7xgP9Ru7JUu4zn";

    }

    public function getToken()
    {
        $iat = time();
        $exp = $iat + (60*60);

        $payload = array(
            "iss" => "127.0.0.1:8000", //issuer
            "aud" => "http://postman.com", //audience
            "iat" => $iat, //token issuance time
            "nbf" => $exp //token expiry time.
        );

        JWT::$leeway = 60;

        
        $jwt = JWT::encode($payload, $this->key, 'HS256');

        return new JsonResponse(["token" => $jwt, "expires" => $exp]);
    }


    public function validateToken($request)
    {
       

        if(in_array('Authorization', $request->getHeaders()))
        {
            $bearerToken = $request->getHeaders()['authorization'][0];
        }else
        {
            return false;
        }

        $token = str_replace('Bearer ', '', $bearerToken);

        JWT::$leeway = 60 * 60 * 24;
        try 
        {
            $decoded = JWT::decode($token, $this->key, array('HS256'));
            return $decoded;

        }catch (Exception $err)
        {
            return false;
        }
       
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
                return new JsonResponse(["errors" => $error], 400);

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