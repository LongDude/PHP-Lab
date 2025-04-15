<?php
namespace src\Controllers;

use src\Files\BaseUploader;
use src\Models\Tariff;
use src\Validators\TariffValidator;
use Fawno\FPDF\FawnoFPDF;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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

    public function getTariffsTable()
    {
        [$filter, $err] = TariffValidator::validateFilter($_GET);
        $list = $this->model->getListFiltered($filter);
        $msg = '';

        if ($err !== '') {
            $_SESSION['error'] = $err;
        }

        if (isset($_GET['type']) && $_GET['type'] == 'pdf') {
            $this->generatePdf($list);
            $msg = "Отчет успешно составлен\n";
        } elseif (isset($_GET['type']) && $_GET['type'] == 'excel') {
            $this->generateExcel($list);
            $msg = "Отчет успешно составлен\n";
        }

        echo $this->twig->render(
            'tariffs.twig',
            [
                'tariffs' => $list,
                'message' => $msg,
                'name' => $filter["name"] ?? "",
                'base_price_from' => $filter["base_price"]["from"] ?? "",
                'base_price_to' => $filter["base_price"]["to"] ?? "",
            ]
        );
    }

    public function getEntries()
    {
        header('Content-type: application/json');
        $list = $this->model->getEntries();
        echo json_encode($list);
    }

    public function get_tariff_form()
    {
        echo $this->twig->render(
            'addTariff.twig',
            [
                'message' => $_SESSION['message'] ?? '',
                'error' => $_SESSION['error'] ?? '',
            ]
        );
    }

    public function post_tariff_form()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: /tariffs/add");
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
        $dist_cost = trim($_POST['dist_cost'] ?? "");

        $success = $this->model->addTariff(
            $name,
            (float) $base_price,
            (float) $base_dist,
            (float) $dist_cost,
        );

        if ($success) {
            $_SESSION['message'] = "New record added!\n";
        } else {
            $_SESSION['message'] = "An error occured\n";
        }
        header("Location: /tariffs/add");
        exit;
    }


    private function generatePdf(array $data)
    {
        $pdf = new FawnoFPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(20, 10, 'Название тарифа', 1);
        $pdf->Cell(20, 10, 'Начальная стоимость', 1);
        $pdf->Cell(20, 10, 'Расстояние в тарифе', 1);
        $pdf->Cell(20, 10, 'Стоимость за км', 1);

        $pdf->Ln();

        $pdf->SetFont('Arial', '', 12);
        foreach ($data as $row) {
            $pdf->Cell(20, 10, $row['name'], 1);
            $pdf->Cell(20, 10, $row['base_price'], 1);
            $pdf->Cell(20, 10, $row['base_dist'], 1);
            $pdf->Cell(20, 10, $row['dist_cost'], 1);
            $pdf->Ln();
        }
        $pdf->Output('D', 'report.pdf');
    }

    private function generateExcel(array $data)
    {
        $spreadsheet = new Spreadsheet();
        $cells = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        $sheet = $spreadsheet->getActiveSheet();

        $i = 0;
        $sheet->setCellValue($cells[$i++] . '1', 'Название тарифа');
        $sheet->setCellValue($cells[$i++] . '1', 'Начальная стоимость');
        $sheet->setCellValue($cells[$i++] . '1', 'Расстояние в тарифе');
        $sheet->setCellValue($cells[$i++] . '1', 'Стоимость за км');

        $rowIndex = 2;
        foreach ($data as $row) {
            $i = 0;
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['name']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['base_price']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['base_dist']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['dist_cost']);
            $rowIndex++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="report.xlsx"');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
?>