<?php
require __DIR__ . '/../vendor/autoload.php';

use src\Controllers\SessionController;
use src\Controllers\UserController;
use src\Core\Router;
use src\Controllers\DriverController;
use src\Controllers\OrderController;
use src\Controllers\TariffController;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;

$router = new Router();
$paths = [__DIR__ . '../src/Entities'];
$dbParams = [
    'driver' => 'mysql',
    'user' => $_ENV['MYSQL_USER'],
    'password' => $_ENV['MYSQL_PASSWORD'],
    'host' => 'db',
    'dbname' => $_ENV["MYSQL_DATABASE"],
    'charset' => 'utf8mb4',
    'driverOptions' => [
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ]
];

$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: $paths,
    isDevMode: true,
);

$connections = DriverManager::getConnection($dbParams, $config);
$em = new EntityManager($connections, $config);

$session = new SessionController($em);
$user = new UserController($em);
$drivers = new DriverController($em);
$orders = new OrderController($em);
$tariffs = new TariffController($em);

// session
$router->get('/', [$session, 'index'], ['client', 'admin', 'driver'], '/login');

// auth
$router->get('/login', [$session, 'get_login']);
$router->post('/login', [$session, 'post_login']);
$router->get('/logout', [$session, 'logout']);

// registration 
$router->get('/register/user', [$user, 'register']);
$router->post('/register/user', [$user, 'register']);

// for registered users - edit profile
$router->get('/editProfile/user', [$user, 'edit'], ['client']);
$router->put('/editProfile/user', [$user, 'edit'], ['client']);

// register - driver (anyone)
$router->get('/register/driver', [$drivers, 'register']);
$router->post('/register/driver', [$drivers, 'register']);

// registered drivers - edit profile
$router->get('/editProfile/driver', [$drivers, 'edit'], ['driver']);
$router->put('/editProfile/driver', [$drivers, 'edit'], ['driver']);

// users - 
$router->get('/orderTazic', [$orders, 'orderTaxi'], ['client', 'driver']);
$router->post('/orderTazic', [$orders, 'orderTaxi'], ['client', 'driver']);
$router->get('/tariffs/list', [$tariffs, 'getTariffsTable'], ['admin']);


// tables for users and drivers
$router->get('/orders/ridesHistory', [$orders, 'getRides'], ['client']);
$router->get('/orders/orderHistory', [$orders, 'getOrders'], ['driver']);

// tables for admin
$router->get('/orders', [$orders, 'index'], ['admin']);
$router->get('/drivers', [$drivers, 'index'], ['admin']);
$router->get('/users', [$user, 'index'], ['admin']);


// tariff manipulation for admin
$router->get('/tariffs', [$tariffs, 'getTariffsTable'], ['admin', 'client']);
$router->get('/tariffs/add', [$tariffs, 'get_tariff_form'], ['admin']);
$router->post('/tariffs/add', [$tariffs, 'post_tariff_form'], ['admin']);

$router->resolve();

$loader = new \Twig\Loader\FilesystemLoader('./../src/views');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
]);
return $twig;
?>