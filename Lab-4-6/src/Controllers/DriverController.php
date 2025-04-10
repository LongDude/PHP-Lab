<?php
namespace src\Controllers;

use PDOException;
use src\Files\BaseUploader;
use src\Models\Driver;
use src\Models\User;
use src\Models\Tariff;
use src\Validators\DriverValidator;

use src\Validators\UserValidator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DriverController
{
    private Driver $driver_model;
    private User $user_model;
    private Environment $twig;

    public function __construct()
    {
        $this->driver_model = new Driver();
        $this->user_model = new User();
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($loader);
    }

    public function getAll()
    {
        $list = $this->driver_model->getList();
        echo $this->twig->render(
            'Tables/drivers_list.twig',
            [
                'drivers' => $list
            ]
        );
    }
    
    public function getEntries()
    {
        header('Content-type: application/json');
        $list = $this->driver_model->getEntries();
        echo json_encode($list);
    }

    public function index()
    {
        [$filter, $err] = DriverValidator::validateFilter($_GET);
        $list = $this->driver_model->getListFiltered($filter);

        if ($err !== '') {
            $_SESSION['error'] = $err;
        }

        echo $this->twig->render(
            'Tables/drivers_table.twig',
            [
                'drivers' => $list,
                'name' => $filter["name"] ?? "",
                'phone' => $filter["phone"] ?? "",
                'email' => $filter["email"] ?? "",
                // 'intership_from' => $filter["intership"]["from"] ?? "",
                // 'intership_to' => $filter["intership"]["to"] ?? "",
                'car_license' => $filter["car_license"] ?? "",
                // 'car_brand' => $filter["car_brand"] ?? "",
                'tariff_id' => $filter["tariff_id"] ?? "",
            ]
        );
    }

    public function register()
    {
        $avaliable_tariffs = new Tariff()->getEntries();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo $this->twig->render(
                'driverForm.twig',
                [
                    'message' => $_SESSION['message'] ?? '',
                    'error' => $_SESSION['error'] ?? '',
                    'avaliable_tariffs' => $avaliable_tariffs,
                    'username' => $_SESSION['username'] ?? '',
                    'email' => $_SESSION['email'] ?? '',
                    'phone' => $_SESSION['phone'] ?? '',
                    'callback' => '/register/driver',
                    'form_title' => 'Регистрация водителя',
                ]
                );
            unset($_SESSION['error']);
            unset($_SESSION['message']);
            exit;
        }

        if (isset($_FILES['csv-file']) && $_FILES['csv-file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['csv-file'];

            $validationErrors = BaseUploader::validateCsv($file, User::fields, new UserValidator());
            $validationErrors .= BaseUploader::validateCsv($file, Driver::fields, new DriverValidator());
            
            if ($validationErrors === "") {
                BaseUploader::saveCsv($file);
                if ($this->driver_model->importCsv(__DIR__ . "/../Files/Uploads/data.csv")) {
                    $_SESSION['message'] = "File uploaded successfully!\n";
                } else {
                    $_SESSION['message'] = "Error uploading data\n";
                }
            } else {
                $_SESSION['error'] .= $validationErrors;
            }
            header("Location: /register/driver");
            exit;
        }

        $validationErrors = UserValidator::validateData($_POST);
        $validationErrors .= DriverValidator::validateData($_POST);
        if ($validationErrors !== "") {
            echo $this->twig->render(
                'driverForm.twig',
                [
                    'message' => "Неккоретный формат входных данных",
                    'error' => $validationErrors,
                    'avaliable_tariffs' => $avaliable_tariffs,
                    'username' => $_SESSION['username'] ?? '',
                    'email' => $_SESSION['email'] ?? '',
                    'phone' => $_SESSION['phone'] ?? '',
                    'callback' => '/register/driver',
                    'form_title' => 'Регистрация водителя',
                ]
                );
            exit;
        }

        $user_id = $this->user_model->getUserId($_POST['email']);
        if ($user_id){
            $success = $this->user_model->updateUser(
                $user_id, 
                $_POST['name'],
                $_POST['phone'],
                $_POST['email'],
                $_POST['password'],
                'driver',
            );
        } else {
            $success = $this->user_model->addUser(
                $_POST['name'],
                $_POST['phone'],
                $_POST['email'],
                $_POST['password'],
                'driver',
            );
            $user_id = $this->user_model->getUserId($_POST['email']);
        }
        
        if (!$success){
            $_SESSION['message'] = "An error occured!\n";
            header("Location: /drivers/register");
        }
        
        $intership = trim($_POST['intership'] ?? "");
        $car_license = trim($_POST['car_license'] ?? "");
        $car_brand = trim($_POST['car_brand'] ?? "");
        $tariff_id = trim($_POST['tariff_id'] ?? "");

        $success &= $this->driver_model->addDriver(
            $user_id, 
            (int) $intership,
            $car_license,
            $car_brand,
            (int) $tariff_id,
        );

        if ($success) {
            $_SESSION['message'] = "New record added!\n";
        } else {
            $_SESSION['message'] = "An error occured\n";
        }
        header("Location: /login");
        exit;
    }

    public function edit(){
        $avaliable_tariffs = new Tariff()->getEntries();

        $driver = $this->driver_model->getDriver($_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo $this->twig->render(
                'driverForm.twig',
                [
                    'message' => $_SESSION['message'] ?? '',
                    'error' => $_SESSION['error'] ?? '',
                    'avaliable_tariffs' => $avaliable_tariffs,
                    'username' => $_SESSION['username'] ?? '',
                    'email' => $_SESSION['email'] ?? '',
                    'phone' => $_SESSION['phone'] ?? '',
                    'intership' => $driver['intership'] ?? '',
                    'car_license' => $driver['car_license'] ?? '',
                    'car_brand' => $driver['car_brand'] ?? '',
                    'tariff_id' => $driver['tariff_id'] ?? '',
                    'callback' => '/editProfile/driver',
                    'form_title' => 'Обновление профиля водителя',
                ]
                );
            unset($_SESSION['error']);
            unset($_SESSION['message']);
            exit;
        }

        if (isset($_FILES['csv-file']) && $_FILES['csv-file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['csv-file'];

            $validationErrors = BaseUploader::validateCsv($file, User::fields, new UserValidator());
            $validationErrors .= BaseUploader::validateCsv($file, Driver::fields, new DriverValidator());
            
            if ($validationErrors === "") {
                BaseUploader::saveCsv($file);
                if ($this->driver_model->importCsv(__DIR__ . "/../Files/Uploads/data.csv")) {
                    $_SESSION['message'] = "File uploaded successfully!\n";
                } else {
                    $_SESSION['message'] = "Error uploading data\n";
                }
            } else {
                $_SESSION['error'] .= $validationErrors;
            }
            header("Location: /register/driver");
            exit;
        }

        $validationErrors = UserValidator::validateData($_POST);
        $validationErrors .= DriverValidator::validateData($_POST);
        if ($validationErrors !== "") {
            echo $this->twig->render(
                'driverForm.twig',
                [
                    'message' => "Неккоретный формат входных данных",
                    'error' => $validationErrors,
                    'avaliable_tariffs' => $avaliable_tariffs,
                    'username' => $_SESSION['username'] ?? '',
                    'email' => $_SESSION['email'] ?? '',
                    'phone' => $_SESSION['phone'] ?? '',
                    'intership' => $driver['intership'] ?? '',
                    'car_license' => $driver['car_license'] ?? '',
                    'car_brand' => $driver['car_brand'] ?? '',
                    'tariff_id' => $driver['tariff_id'] ?? '',
                    'callback' => '/editProfile/driver',
                    'form_title' => 'Обновление профиля водителя',
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
            'driver',
        );
        
        if (!$success){
            $_SESSION['message'] = "New record added!\n";
            header("Location: /drivers/register");
        } else {
            $_SESSION['username'] = $_POST['name'];
            $_SESSION['email'] = $_POST['phone'];
            $_SESSION['phone'] = $_POST['email'];
            $_SESSION['role'] = 'driver';
        }
        
        $intership = trim($_POST['intership'] ?? "");
        $car_license = trim($_POST['car_license'] ?? "");
        $car_brand = trim($_POST['car_brand'] ?? "");
        $tariff_id = trim($_POST['tariff_id'] ?? "");

        $success &= $this->driver_model->addDriver(
            $_SESSION['user_id'], 
            (int) $intership,
            $car_license,
            $car_brand,
            (int) $tariff_id,
        );

        if ($success) {
            $_SESSION['message'] = "New record added!\n";
        } else {
            $_SESSION['message'] = "An error occured\n";
        }
        header("Location: /");
        exit;
    }

}
?>