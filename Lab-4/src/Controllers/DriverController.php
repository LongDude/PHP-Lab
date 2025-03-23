<?php
namespace Src\Controllers;

use PDOException;
use Src\Files\BaseUploader;
use Src\Models\Driver;
use Src\Validators\DriverValidator;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DriverController
{
    private Driver $model;
    private Environment $twig;

    public function __construct()
    {
        $this->model = new Driver();
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($loader);
    }

    public function getAll()
    {
        $list = $this->model->getList();
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
        $list = $this->model->getEntries();
        echo json_encode($list);
    }

    public function index()
    {
        session_start();

        [$filter, $err] = DriverValidator::validateFilter($_GET);
        $list = $this->model->getListFiltered($filter);

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
                'intership_from' => $filter["intership"]["from"] ?? "",
                'intership_to' => $filter["intership"]["to"] ?? "",
                'car_license' => $filter["car_license"] ?? "",
                'car_brand' => $filter["car_brand"] ?? "",
                'tariff_id' => $filter["tariff_id"] ?? "",
            ]
        );
    }

    public function form()
    {
        include __DIR__ . '/../views/driver_form.php';
    }

    public function addDriver()
    {
        session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /drivers/add");
            exit;
        }

        if (isset($_FILES['csv-file']) && $_FILES['csv-file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['csv-file'];

            $validationErrors = BaseUploader::validateCsv($file, Driver::fields, new DriverValidator());
            if ($validationErrors === "") {
                BaseUploader::saveCsv($file);
                if ($this->model->importCsv(__DIR__ . "/../Files/Uploads/data.csv")) {
                    $_SESSION['message'] = "File uploaded successfully!\n";
                } else {
                    $_SESSION['message'] = "Error uploading data\n";
                }
            } else {
                $_SESSION['error'] .= $validationErrors;
            }
            header("Location: /drivers/add");
            exit;
        }
        $validationErrors = DriverValidator::validateData($_POST);
        if ($validationErrors !== "") {
            $_SESSION['error'] = $validationErrors;
            header("Location: /drivers/add");
            exit;
        }

        $name = trim($_POST['name'] ?? "");
        $phone = trim($_POST['phone'] ?? "");
        $email = trim($_POST['email'] ?? "");
        $intership = trim($_POST['intership'] ?? "");
        $car_license = trim($_POST['car_license'] ?? "");
        $car_brand = trim($_POST['car_brand'] ?? "");
        $tariff_id = trim($_POST['tariff_id'] ?? "");

        $success = $this->model->addDriver(
            $name,
            $phone,
            $email,
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
        header("Location: /drivers/add");
        exit;
    }
}
?>