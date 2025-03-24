<?php
namespace src\Controllers;

use src\Files\BaseUploader;
use src\Models\Driver;
use src\Models\Order;
use src\Models\Tariff;
use src\Validators\OrderValidator;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class OrderController
{
    private Order $model;
    private Environment $twig;

    public function __construct()
    {
        $this->model = new Order();
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($loader);
    }

    public function getAll()
    {
        $list = $this->model->getList();
        echo $this->twig->render(
            'Tables/orders_list.twig',
            [
                'orders' => $list
            ]
        );
    }


    public function index()
    {
        session_start();

        [$filter, $err] = OrderValidator::validateFilter($_GET);
        $list = $this->model->getListFiltered($filter);

        if ($err !== '') {
            $_SESSION['error'] = $err;
        }


        $drivers_list = new Driver() -> getEntries();
        $tariffs_list = new Tariff() -> getEntries();

        echo $this->twig->render(
            'Tables/orders_table.twig',
            [
                'drivers_entries' => $drivers_list,
                'tariffs_entries' => $tariffs_list,
                'orders' => $list,
                'orderedAt_from' => $filter["orderedAt"]['from'] ?? "",
                'orderedAt_to' => $filter["orderedAt"]['to'] ?? "",
                'tariff_id' => $filter["tariff_id"] ?? "",
                'driver_id' => $filter["driver_id"] ?? "",
            ]
        );
    }

    public function form()
    {
        include __DIR__ . '/../views/Forms/order_form.php';
    }

    public function addOrder()
    {
        session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /orders/add");
            exit;
        }

        if (isset($_FILES['csv-file']) && $_FILES['csv-file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['csv-file'];

            $validationErrors = BaseUploader::validateCsv($file, Order::fields, new OrderValidator());
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
            header("Location: /orders/add");
            exit;
        }
        $validationErrors = OrderValidator::validateData($_POST);
        if ($validationErrors !== "") {
            $_SESSION['error'] = $validationErrors;
            header("Location: /orders/add");
            exit;
        }

        $phone = trim($_POST['phone'] ?? "");
        $from_loc = trim($_POST['from_loc'] ?? "");
        $dest_loc = trim($_POST['dest_loc'] ?? "");
        $distance = trim($_POST['distance'] ?? "");
        $driver_id = trim($_POST['driver_id'] ?? "");
        $tariff_id = trim($_POST['tariff_id'] ?? "");

        $success = $this->model->addOrder(
            $phone,
            $from_loc,
            $dest_loc,
            (float) $distance,
            (int) $driver_id,
            (int) $tariff_id,
        );

        if ($success) {
            $_SESSION['message'] = "New record added!\n";
        } else {
            $_SESSION['message'] = "An error occured\n";
        }
        header("Location: /orders/add");
        exit;
    }
}
?>