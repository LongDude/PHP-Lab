<?php
namespace Src\Controllers;

use Src\Files\BaseUploader;
use Src\Models\Tariff;
use Src\Validators\TariffValidator;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TariffController
{
    private Tariff $model;
    private Environment $twig;

    public function __construct()
    {
        $this->model = new Tariff();
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($loader);
    }

    public function getAll()
    {
        $list = $this->model->getList();
        echo $this->twig->render(
            'Tables/tariffs_list.twig',
            [
                'tariffs' => $list
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

        [$filter, $err] = TariffValidator::validateFilter($_GET);
        $list = $this->model->getListFiltered($filter);

        if ($err !== '') {
            $_SESSION['error'] = $err;
        }

        echo $this->twig->render(
            'Tables/tariffs_table.twig',
            [
                'tariffs' => $list,
                'name' => $filter["name"] ?? "",
                'base_price_from' => $filter["base_price"]["from"] ?? "",
                'base_price_to' => $filter["base_price"]["to"] ?? "",
            ]
        );
    }

    public function form()
    {
        include __DIR__ . '/../views/tariff_form.php';
    }

    public function addTariff()
    {
        session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /tariffs/add");
            exit;
        }

        if (isset($_FILES['csv-file']) && $_FILES['csv-file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['csv-file'];

            $validationErrors = BaseUploader::validateCsv($file, Tariff::fields, new TariffValidator());
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
            header("Location: /tariffs/add");
            exit;
        }
        $validationErrors = TariffValidator::validateData($_POST);
        if ($validationErrors !== "") {
            $_SESSION['error'] = $validationErrors;
            header("Location: /tariffs/add");
            exit;
        }

        $name = trim($_POST['name'] ?? "");
        $base_price = trim($_POST['base_price'] ?? "");
        $base_dist = trim($_POST['base_dist'] ?? "");
        $base_time = trim($_POST['base_time'] ?? "");
        $dist_cost = trim($_POST['dist_cost'] ?? "");
        $time_cost = trim($_POST['time_cost'] ?? "");

        $success = $this->model->addTariff(
            $name,
            (float) $base_price,
            (float) $base_dist,
            (float) $base_time,
            (float) $dist_cost,
            (float) $time_cost,
        );

        if ($success) {
            $_SESSION['message'] = "New record added!\n";
        } else {
            $_SESSION['message'] = "An error occured\n";
        }
        header("Location: /tariffs/add");
        exit;
    }
}
?>