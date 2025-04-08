<?php
require __DIR__ . '/../vendor/autoload.php';

use src\Controllers\SessionController;
use src\Controllers\UserController;
use src\Core\Router;
use src\Controllers\DriverController;
use src\Controllers\OrderController;
use src\Controllers\TariffController;
$router = new Router();


$session = new SessionController();
$user = new UserController();
$drivers = new DriverController();
$orders = new OrderController();
$tariffs = new TariffController();

// session
$router->get('/', [$session, 'index']);

// auth
$router->get('/login', [$session, 'login']);
$router->post('/login', [$session, 'login']);
$router->get('/logout', [$session, 'logout']);


// registration 
$router->get('/register/user', [$user, 'register']);
$router->post('/register/user', [$user, 'register']);

// for registered users - edit profile
$router->get('/editProfile/user', [$user, 'edit']);
$router->put('/editProfile/user', [$user, 'edit']);

// register - driver (anyone)
$router->get('/register/driver', [$drivers, 'register']);
$router->post('/register/driver', [$drivers, 'register']);

// registered drivers - edit profile
$router->get('/editProfile/driver', [$drivers, 'edit']);
$router->put('/editProfile/driver', [$drivers, 'edit']);

// users - 
$router->get('/orderTazic', [$orders, 'orderTaxi']);
$router->post('/orderTazic', [$orders, 'orderTaxi']);

// tables for users and drivers
$router->get('/orders/ridesHistory', [$orders, 'getRides']);
$router->get('/orders/orderHistory', [$orders, 'getOrders']);

// tables for admin
$router->get('/orders', [$orders, 'index']);
$router->get('/drivers', [$drivers, 'index']);
$router->get('/users', [$users, 'index']);


// tariff manipulation for admin
$router->get('/tariffs', [$tariffs, 'index']);
$router->get('/tariffs/add', [$tariffs, 'form']);
$router->post('/tariffs/add', [$tariffs, 'addTariff']);

// ! OLD ARHCITECTURE
// $router->get('/drivers/add', [$drivers, 'form']);
// $router->post('/drivers/add', [$drivers, 'addDriver']);
// $router->get('/orders/all', [$orders, 'getAll']);

// $router->get('/orders/add', [$orders, 'form']);
// $router->post('/orders/add', [$orders, 'addOrder']);
// $router->get('/orderTazic', [$orders, 'orderTaxi']);


$router->resolve();

$loader = new \Twig\Loader\FilesystemLoader('./../src/views');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
]);
return $twig;
?>