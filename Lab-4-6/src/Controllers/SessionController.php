<?php

namespace src\Controllers;
use Doctrine\ORM\EntityManager;
use PDOException;
use src\Files\BaseUploader;

use src\Entities\User;
use src\Validators\BaseValidators;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class SessionController{

    private $user_rep;
    private Environment $twig;

    public function __construct(EntityManager $em)
    {
        $this->user_rep = $em->getRepository(User::class);
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
        $user = $this->user_rep->findOneBy(array('email' => $_SESSION['email']));
        if (!$user || md5($password) != $user->getPassword()){
            $_SESSION['message'] = "Неправильное имя пользователя или пароль";
            header("Location: /login");
            exit;
        }

        // Авторизация прошла удачно
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['username'] = $user->getName();
        $_SESSION['phone'] = $user->getPhone();
        $_SESSION['role'] = $user->getRole();
        header("Location: /");
    }
    public function logout(){
        session_unset();
        session_destroy();
        header("Location: /login");
    }
}
?>