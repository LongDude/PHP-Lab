<?php

namespace src\Controllers;
use PDOException;
use src\Files\BaseUploader;

use src\Models\User;
use src\Validators\BaseValidators;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class SessionController{

    private User $user_model;
    private Environment $twig;

    public function __construct()
    {
        $this->user_model = new User();
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($loader);
    }

    public function index(){
        echo $this->twig->render(
            'main.twig',
            [
                'message' => $_SESSION['message'] ?? '',
                'error' => $_SESSION['error'] ?? '',
                'username' => $_SESSION['username'] ?? '',
                'email' => $_SESSION['email'] ?? '',
                'phone' => $_SESSION['phone'] ?? '-',
                'role' => $_SESSION['role'] ?? '',
            ]
        );
        unset($_SESSION['error']);
        unset($_SESSION['message']);
    }
    public function get_login(){
        echo $this->twig->render(
            'login.twig',
            [
                'message' => $_SESSION['message'] ?? '',
                'error' => $_SESSION['error'] ?? '',
                'email' => $_SESSION['email'] ?? '',
            ]
        );
        unset($_SESSION['error']);
        unset($_SESSION['message']);
    }

    public function post_login(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /login");
            exit;
        }
        $_SESSION['email'] = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $validationErrors = BaseValidators::emailValidator($_SESSION['email']);
        if (strlen($password) == 0){
            $validationErrors .= "INVALID password DATA;";
        }
        if ($validationErrors != ""){
            $_SESSION['error'] = $validationErrors;
            $_SESSION['message'] = "Ошибка входа: некорректный формат ввода";
            header("Location: /login");
            exit;
        }
        $user = $this->user_model->indetificate($_SESSION['email']);
        if (!$user || md5($password) != $user['password']){
            $_SESSION['message'] = "Неправильное имя пользователя или пароль";
            header("Location: /login");
            exit;
        }

        // Авторизация прошла удачно
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['name'];
        $_SESSION['phone'] = $user['phone'];
        $_SESSION['role'] = $user['role'];
        header("Location: /");
    }
    public function logout(){
        session_unset();
        session_destroy();
        header("Location: /login");
    }
}
?>