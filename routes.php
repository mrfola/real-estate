<?php
use MiladRahimi\PhpRouter\Router;
use API\V1\Controllers\ListingController;
use API\V1\Controllers\UserController;
use API\V1\Controllers\AuthController;

$router = Router::create();

$router->get('/', [ListingController::class, 'index']); //should eventually be login url or something

$router->get('/listings', [ListingController::class, 'index']);
$router->get('/listings/{id}', [ListingController::class, 'show']);
$router->post('/listings', [ListingController::class, 'create']);
$router->patch('/listings/{id}', [ListingController::class, 'update']);
$router->delete('/listings/{id}', [ListingController::class, 'destroy']);

$router->get('/users', [UserController::class, 'index']);
$router->get('/users/{id}', [UserController::class, 'show']);
$router->post('/users', [UserController::class, 'create']);
$router->patch('/users/{id}', [UserController::class, 'update']);
$router->delete('/users/{id}', [UserController::class, 'destroy']);

$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->dispatch();