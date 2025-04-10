<?php

namespace src\Controllers;
use PDOException;
use src\Files\BaseUploader;
use src\Models\User;

use src\Validators\UserValidator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class UserController{
    private User $user_model;
    private Environment $twig;

    public function __construct()
    {
        $this->user_model = new User();
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($loader);
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo $this->twig->render(
                'userForm.twig',
                [
                    'message' => $_SESSION['message'] ?? '',
                    'error' => $_SESSION['error'] ?? '',
                    'callback' => '/register/user',
                    'form_title' => 'Регистрация пользователя',
                ]
                );
            unset($_SESSION['error']);
            unset($_SESSION['message']);
            exit;
        }

        if (isset($_FILES['csv-file']) && $_FILES['csv-file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['csv-file'];

            $validationErrors = BaseUploader::validateCsv($file, User::fields, new UserValidator());
            if ($validationErrors === "") {
                BaseUploader::saveCsv($file);
                if ($this->user_model->importCsv(__DIR__ . "/../Files/Uploads/data.csv")) {
                    $_SESSION['message'] = "File uploaded successfully!\n";
                } else {
                    $_SESSION['message'] = "Error uploading data\n";
                }
            } else {
                $_SESSION['error'] .= $validationErrors;
            }
            header("Location: /register/user");
            exit;
        }

        $validationErrors = UserValidator::validateData($_POST);
        if ($validationErrors !== ""){
            echo $this->twig->render(
                'userForm.twig',
                [
                    'username' => $_POST['username'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'phone' => $_POST['phone'] ?? '',
                    'message' => "Неккоретный формат входных данных",
                    'error' => $validationErrors,
                    'callback' => '/register/user',
                    'form_title' => 'Регистрация пользователя',
                ]
                );
            exit;
        }

        $success = $this->user_model->addUser(
            $_POST['name'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['password'],
            'user',
        );

        if ($success) {
            $_SESSION['message'] = "Регистрация успешна\n";
        }
        else {
            $_SESSION['message'] = "Ошибка при регистрации\n";
        }
        header("Location: /login");
        exit;
    }

    public function edit(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo $this->twig->render(
                'userForm.twig',
                [
                    'username' => $_SESSION['username'] ?? '',
                    'email' => $_SESSION['email'] ?? '',
                    'phone' => $_SESSION['phone'] ?? '',
                    'message' => $_SESSION['message'] ?? '',
                    'error' => $_SESSION['error'] ?? '',
                    'callback' => '/editProfile/user',
                    'form_title' => 'Редактирование пользователя',
                ]
                );
            unset($_SESSION['error']);
            unset($_SESSION['message']);
            exit;
        }

        $validationErrors = UserValidator::validateData($_POST);
        if ($validationErrors !== ""){
            echo $this->twig->render(
                'userForm.twig',
                [
                    'username' => $_POST['name'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'phone' => $_POST['phone'] ?? '',
                    'message' => "Неккоретный формат входных данных",
                    'error' => $validationErrors,
                    'callback' => '/editProfile/user',
                    'form_title' => 'Редактирование пользователя',
                ]
                );
            exit;
        }

        $success = $this->user_model->updateUser(
            $_SESSION['user_id'],
            $_POST['name'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['password'],
            'user',
        );

        if ($success) {
            $_SESSION['message'] = "Данные успешно обновлены\n";
            $_SESSION['username'] = $_POST['name'];
            $_SESSION['email'] = $_POST['phone'];
            $_SESSION['phone'] = $_POST['email'];
        }
        else {
            $_SESSION['message'] = "Ошибка при обновлении данных\n";
        }
        header("Location: /");
        exit;
    }

    public function index(){
        [$filter, $err] = UserValidator::validateFilter($_GET);
        $list = $this->user_model->getListFiltered($filter);

        if ($err !== '') {
            $_SESSION['error'] = $err;
        }

        echo $this->twig->render(
            'users.twig',
            [
                'users' => $list,
                'name' => $filter["name"] ?? "",
                'phone' => $filter["phone"] ?? "",
                'email' => $filter["email"] ?? "",
            ]
        );
    }
}
?>