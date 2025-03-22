<?php
require __DIR__ . '/../vendor/autoload.php';

use Src\Core\Router;
use Src\Controllers\DriverController;
use Src\Controllers\OrderController;
use Src\Controllers\TariffController;

$router = new Router();

$drivers = new DriverController();
$router->get('/drivers', [$drivers, 'index']);
$router->get('/drivers/add', [$drivers, 'addForm']);
$router->post('/drivers/add', [$drivers, 'add']);

$orders = new OrderController();
$router->get('/orders', [$orders, 'index']);
$router->get('/orders/add', [$orders, 'addForm']);
$router->post('/orders/add', [$orders, 'add']);

$tariffs = new TariffController();
$router->get('/tariffs', [$tariffs, 'index']);
$router->get('/tariffs/add', [$tariffs, 'addForm']);
$router->post('/tariffs/add', [$tariffs, 'add']);

$router->resolve();

$loader = new \Twig\Loader\FilesystemLoader('./../src/Views');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
]);
return $twig;
?>