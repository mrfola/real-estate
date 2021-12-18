<?php

namespace API\V1\Controllers;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response\JsonResponse;
use Core\DB;
use PDO;
use Valitron\Validator;
use Exception;
use Firebase\JWT\JWT; //JsonWebToken package by Firebase


class AuthController
{
    private $con, $key;
    static $user;

    public function __construct()
    {
        $this->con = DB::getConnection();
        $this->key = "ITuCY0SgavWs5MIqf5642Fk0hnW8JkoKifNM8XclZtLXKqPlWkfRgOBGaQm3mJyT3m8lOfkqu0wR29tq4Yt1uH7xgP9Ru7JUu4zn";

    }

    public function getToken(array $data)
    {
        $iat = time();
        $exp = $iat + (60*60);

        $payload = array(
            "iss" => "localhost:8000", //issuer
            "aud" => "http://postman.com", //audience
            "iat" => $iat, //token issuance time
            "nbf" => $exp, //token expiry time.
            "data" => $data
        );

        JWT::$leeway = 60;

        
        $jwt = JWT::encode($payload, $this->key, 'HS256');

        return new JsonResponse(["token" => $jwt, "expires" => $exp]);
    }


    public function validateRequest($request)
    {

        if($request->getHeader('Authorization'))
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

    public function set_user($user)
    {
        self::$user= $user;
    }

    public static function get_user()
    {
        return self::$user;
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
            $statement = $this->con->prepare("SELECT `password`,`id` FROM `users` WHERE email=:email");
            $statement->bindValue(":email", $data['email']);
            $statement->execute();
            $user_data = $statement->fetch(PDO::FETCH_ASSOC);

            if($user_data)
            {
                if(password_verify($data['password'], $user_data["password"]))
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
                return $this->getToken(["id" => $user_data["id"]]);

            }else
            {
                //error message
                $error = "Wrong username or password";
                return new JsonResponse(["errors" => $error], 400);

            }
        }else
        {
            return new JsonResponse(["errors" => $validator->errors()], 504);
        }
    }

    public function logout(ServerRequest $request)
    {
        if ($this->validateRequest($request))
        {
            return new JsonResponse([
                "message" => "Logout Successful"
            ], 200);
        }else
        {
            return new JsonResponse([
                "error" => "There was a problem with your request"
            ], 400);
        }

    }
}