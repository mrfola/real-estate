<?php
namespace API\V1\Controllers;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response\JsonResponse;
use Valitron\Validator;
use Exception;
use API\V1\Models\User;

class UserController
{
    public function show($id)
    {
        $user = new User();
        return $user->getUser($id);
    }

    public function create(ServerRequest $request)
    {
        $data = $request->getParsedBody(); //get array data from request

        //Validate
        $validate = new Validator($data);
        $validate->rule('required', ['name', 'email', 'password']);
        $validate->rule('email', 'email');

        if ($validate->validate())
        {   
            $user = new User();
            return $user->createUser($data);

        }
        else
        {   
          return new JsonResponse(["errors" => $validate->errors()]);  
        }
    }
}