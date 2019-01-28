<?php

use Phalcon\Mvc\Micro\Collection as RouteHandler;

//version routes
$router = new RouteHandler();
$router->setHandler('App\Controllers\VersionController', true);
$router->get('/', 'index');
$app->mount($router);

//auth routes
$router = new RouteHandler();
$router->setHandler('App\Controllers\AuthController', true);
$router->setPrefix('/api/v1/oauth');
$router->post('/token', 'token');
$router->get('/authorize', 'authorize');
$router->post('/token/validate', 'validate');
$app->mount($router);

//$router = $di->getRouter(false);

$router = new RouteHandler();
$router->setHandler('App\Controllers\PhonesController', true);

$router->post('/get','get');
$router->post('/getById','getById');
$router->post('/getByName','getByName');
$router->post('/save','save');
$router->post('/update','update');
$router->post('/delete','delete');

$app->mount($router);