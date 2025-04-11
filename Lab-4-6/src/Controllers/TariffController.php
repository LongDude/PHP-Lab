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

        if ($err !== '') {
            $_SESSION['error'] = $err;
        }

        echo $this->twig->render(
            'tariffs.twig',
            [
                'tariffs' => $list,
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
        $base_time = trim($_POST['base_time'] ?? "");
        $dist_cost = trim($_POST['dist_cost'] ?? "");
        $time_cost = trim($_POST['time_cost'] ?? "");

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


    private function generatePdf(array $data, string $reportType)
    {
        $pdf = new FawnoFPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        if ($reportType != 'history') {
            $pdf->Cell(20, 10, 'Телефон клиента', 1);
        }
        $pdf->Cell(20, 10, 'Начальная точка', 1);
        $pdf->Cell(20, 10, 'Конечная точка', 1);
        $pdf->Cell(20, 10, 'Расстояние', 1);
        $pdf->Cell(20, 10, 'Время заказа', 1);

        if ($reportType != 'rides') {
            $pdf->Cell(20, 10, 'Имя водителя', 1);
        }
        if ($reportType == 'full') {
            $pdf->Cell(20, 10, 'Имя клиента', 1);
        }

        $pdf->Cell(20, 10, 'Тарифф', 1);
        $pdf->Cell(20, 10, 'Стоимость', 1);

        $pdf->Ln();

        $pdf->SetFont('Arial', '', 12);
        foreach ($data as $row) {
            if ($reportType != 'history') {
                $pdf->Cell(20, 10, $row['phone'], 1);
            }
            $pdf->Cell(20, 10, $row['from_loc'], 1);
            $pdf->Cell(20, 10, $row['dest_loc'], 1);
            $pdf->Cell(20, 10, $row['distance'], 1);
            $pdf->Cell(20, 10, $row['orderedAt'], 1);

            if ($reportType != 'rides') {
                $pdf->Cell(20, 10, $row['driver_name'], 1);
            }
            if ($reportType == 'full') {
                $pdf->Cell(20, 10, $row['user_name'], 1);
            }

            $pdf->Cell(20, 10, $row['tariff_name'], 1);
            $pdf->Cell(20, 10, $row['price'], 1);
            $pdf->Ln();
        }
        $pdf->Output('D', 'report.pdf');
    }

    private function generateExcel(array $data, string $reportType)
    {
        $spreadsheet = new Spreadsheet();
        $cells = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        $sheet = $spreadsheet->getActiveSheet();

        $i = 0;
        if ($reportType != 'history') {
            $sheet->setCellValue($cells[$i++] . '1', 'Номер телефона');
        }
        $sheet->setCellValue($cells[$i++] . '1', 'Начальная точка');
        $sheet->setCellValue($cells[$i++] . '1', 'Конечная точка');
        $sheet->setCellValue($cells[$i++] . '1', 'Расстояние');
        $sheet->setCellValue($cells[$i++] . '1', 'Время заказа');
        if ($reportType != 'rides') {
            $sheet->setCellValue($cells[$i++] . '1', 'Имя водителя');
        }
        if ($reportType == 'full') {
            $sheet->setCellValue($cells[$i++] . '1', 'Имя клиента');
        }
        $sheet->setCellValue($cells[$i++] . '1', 'Тариф');
        $sheet->setCellValue($cells[$i++] . '1', 'Стоимость');

        $rowIndex = 2;
        foreach ($data as $row) {
            $i = 0;
            if ($reportType != 'history') {
                $sheet->setCellValue($cells[$i++] . $rowIndex, $row['phone']);
            }
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['from_loc']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['dest_loc']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['distance']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['orderedAt']);
            if ($reportType != 'rides') {
                $sheet->setCellValue($cells[$i++] . $rowIndex, $row['driver_name']);
            }
            if ($reportType == 'full') {
                $sheet->setCellValue($cells[$i++] . $rowIndex, $row['user_name']);
            }
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['tariff_name']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['price']);
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