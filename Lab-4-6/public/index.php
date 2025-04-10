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
$router->get('/', [$session, 'index'], ['user', 'admin', 'driver'], '/login');

// auth
$router->get('/login', [$session, 'get_login']);
$router->post('/login', [$session, 'post_login']);
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
$router->get('/orderTazic', [$orders, 'orderTaxi'], ['user', 'admin', 'driver']);
$router->post('/orderTazic', [$orders, 'orderTaxi'], ['user', 'admin', 'driver']);
$router->get('/tariffs/list', [$tariffs, 'getTariffsTable'], ['admin']);


// tables for users and drivers
$router->get('/orders/ridesHistory', [$orders, 'getRides']);
$router->get('/orders/orderHistory', [$orders, 'getOrders']);

// tables for admin
$router->get('/orders', [$orders, 'index'], ['admin']);
$router->get('/drivers', [$drivers, 'index'], ['admin']);
$router->get('/users', [$user, 'index'], ['admin']);


// tariff manipulation for admin
$router->get('/tariffs', [$tariffs, 'getTariffsTable'], ['admin']);
$router->get('/tariffs/add', [$tariffs, 'get_tariff_form'], ['admin']);
$router->post('/tariffs/add', [$tariffs, 'post_tariff_form'], ['admin']);

$router->resolve();

$loader = new \Twig\Loader\FilesystemLoader('./../src/views');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
]);
return $twig;
?>