<?php
use MiladRahimi\PhpRouter\Router;
use API\V1\Controllers\ListingController;

$router = Router::create();

$router->get('/', [ListingController::class, 'index']); //should eventually be login url or something

$router->get('/listings', [ListingController::class, 'index']);
$router->get('/listings/{id}', [ListingController::class, 'show']);
$router->post('/listings', [ListingController::class, 'create']);
$router->patch('/listings/{id}', [ListingController::class, 'update']);
$router->delete('/listings/{id}', [ListingController::class, 'destroy']);

$router->dispatch();