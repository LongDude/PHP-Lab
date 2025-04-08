<?php
require __DIR__ . '/../vendor/autoload.php';

use src\Core\Router;
use src\Controllers\DriverController;
use src\Controllers\OrderController;
use src\Controllers\TariffController;
$router = new Router();

$drivers = new DriverController();
$router->get('/drivers', [$drivers, 'index']);
$router->get('/drivers/entries', [$drivers, 'getEntries']);
$router->get('/drivers/all', [$drivers, 'getAll']);
$router->get('/drivers/add', [$drivers, 'form']);
$router->post('/drivers/add', [$drivers, 'addDriver']);

$orders = new OrderController();
$router->get('/orders', [$orders, 'index']);
$router->get('/orders/all', [$orders, 'getAll']);
$router->get('/orders/add', [$orders, 'form']);
$router->post('/orders/add', [$orders, 'addOrder']);
$router->get('/orderTazic', [$orders, 'orderTaxi']);
$router->post('/orderTazic', [$orders, 'orderTaxi']);


$tariffs = new TariffController();
$router->get('/tariffs', [$tariffs, 'index']);
$router->get('/tariffs/entries', [$tariffs, 'getEntries']);
$router->get('/tariffs/all', [$tariffs, 'getAll']);
$router->get('/tariffs/add', [$tariffs, 'form']);
$router->post('/tariffs/add', [$tariffs, 'addTariff']);

$router->resolve();

$loader = new \Twig\Loader\FilesystemLoader('./../src/views');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
]);
return $twig;
?>