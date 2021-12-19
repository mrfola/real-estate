<?php

namespace API\V1\Middlewares;

use MiladRahimi\PhpRouter\Router;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use API\V1\Controllers\AuthController;
use Closure;

class AuthMiddleware 
{
    private $auth;

    public function handle(ServerRequestInterface $request, Closure $next)
    {
        $this->auth = new AuthController();

        //ensures user is logged in
        if ($this->auth->validateRequest($request))
        {     
            $user_id = $this->auth->validateRequest($request)->data->id;
            $this->auth->setUserId($user_id); //storing auth user id in authController so the value can be used to validate requests in other controllers      
            // Call the next middleware/controller
            return $next($request);
        }

        return new JsonResponse(['error' => 'You need to login first'], 401);
    }
}