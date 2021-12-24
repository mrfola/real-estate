<?php
use MiladRahimi\PhpRouter\Router;
use API\V1\Controllers\ListingController;
use API\V1\Controllers\UserController;
use API\V1\Controllers\AuthController;
use API\V1\Middlewares\AuthMiddleware;
use API\V1\Controllers\PaymentController;

$router = Router::create();

$router->get('/', [ListingController::class, 'index']); //should eventually be login url or something

$router->group(['middleware' => [AuthMiddleware::class]], function(Router $router) {

$router->get('/listings', [ListingController::class, 'index']);
$router->get('/listings/{id}', [ListingController::class, 'show']);
$router->post('/listings', [ListingController::class, 'create']);
$router->patch('/listings/{id}', [ListingController::class, 'update']);
$router->delete('/listings/{id}', [ListingController::class, 'destroy']);

$router->get('/users', [UserController::class, 'index']);
$router->get('/users/{id}', [UserController::class, 'show']);
$router->patch('/users/{id}', [UserController::class, 'update']);
$router->delete('/users/{id}', [UserController::class, 'destroy']);

$router->post('/pay/listing/{listing_id}', [PaymentController::class, 'pay']);

$router->get('/logout', [AuthController::class, 'logout']);
});

$router->get('/pay_redirect', [PaymentController::class, 'pay_redirect']);

$router->post('/users', [UserController::class, 'create']);
$router->post('/login', [AuthController::class, 'login']);


$router->dispatch();