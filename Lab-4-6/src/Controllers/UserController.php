<?php

namespace src\Controllers;
use PDOException;
use src\Files\BaseUploader;
use src\Models\User;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class UserController{
    private User $model;
    private Environment $twig;

    public function __construct()
    {
        $this->model = new User();
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($loader);
    }

    public function login(){
        // TODO
        session_start();
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            include __DIR__ . '/../views/Forms/login.php';
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        }
    }

    public function logout(){
        session_start();
        session_unset();
        session_destroy();
        header("Location: /login");
    }

    public function register()
    {
        // If _GET['asDriver']: register driver
        session_start();

    }

    public function edit(){
        
    }
}
?>