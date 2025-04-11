<?php
namespace src\Controllers;

use Fawno\FPDF\FawnoFPDF;
use Mgrn\Tfpdf\Pdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use src\Files\BaseUploader;
use src\Models\Driver;
use src\Models\Order;
use src\Models\Tariff;
use src\Validators\OrderValidator;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

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

    public function index()
    {
        [$filter, $err] = OrderValidator::validateFilter($_GET);
        $list = $this->model->getListFiltered($filter);
        $msg = '';

        $tariffs_list = new Tariff()->getEntries();
        if (isset($_GET['type']) && $_GET['type'] === "pdf") {
            $this->generatePdf($list, 'full');
            $msg = "Отчет успешно составлен\n";
        } elseif (isset($_GET['type']) && $_GET['type'] == 'excel') {
            $this->generateExcel($list, 'full');
            $msg = "Отчет успешно составлен\n";
        }

        echo $this->twig->render(
            'orders.twig',
            [
                'tariffs_entries' => $tariffs_list,
                'error' => $err,
                'message' => $msg,
                'orders' => $list,
                'orderedAt_from' => $filter['orderedAt']['from'] ?? '',
                'orderedAt_to' => $filter['orderedAt']['to'] ?? '',
                'tariff_id' => $filter['tariff_id'] ?? '',
                'name' => $filter['name'] ?? '',
                'callback' => '/orders',
                'type' => 'full',
                'title' => 'Таблица заказов',
            ]
        );
    }

    public function orderTaxi()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $filter = array();
            if (isset($_GET['rating_from'])) {
                if ($_GET['rating_from'] >= 0 and $_GET['rating_from'] <= 5) {
                    $filter['rating']['from'] = $_GET['rating_from'];
                }
            }

            if (isset($_GET['tariff_id']) and $_GET['tariff_id'] > 0) {
                $filter['tariff_id'] = $_GET['tariff_id'];
            }

            $list = $this->model->getAvaliableRides($filter);
            $avaliable_tariffs = new Tariff()->getEntries();

            echo $this->twig->render(
                'orderTaxi.twig',
                [
                    'avaliable_orders' => $list,
                    'avaliable_tariffs' => $avaliable_tariffs,
                    'rating_from' => $filter['rating']['from'] ?? '',
                    'tariff_id' => $filter['tariff_id'] ?? '',
                    'type' => 'full',
                ]
            );
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $driver_id = trim($_POST['driver_id'] ?? '');
            $begin = trim($_POST['startPoint'] ?? '');
            $destination = trim($_POST['endPoint'] ?? '');
            $distance = trim($_POST['distance'] ?? '');

            $success = $this->model->addOrder(
                $begin,
                $destination,
                $distance,
                $driver_id,
                $_SESSION['user_id'],
            );
            if ($success) {
                $_SESSION['message'] = "New record added!\n";
            } else {
                $_SESSION['message'] = "An error occured\n";
            }
            http_response_code(200);
            exit;
        }
    }

    public function getRides()
    {
        [$filter, $err] = OrderValidator::validateFilter($_GET);
        $list = $this->model->getListFiltered($filter, $_SESSION['user_id']);

        $tariffs_list = new Tariff()->getEntries();

        if ($_GET['type'] == 'pdf') {
            $this->generatePdf($list, 'rides');
            $msg = "Отчет успешно составлен\n";
        } elseif ($_GET['type'] == 'excel') {
            $this->generatePdf($list, 'rides');
            $msg = "Отчет успешно составлен\n";
        }

        echo $this->twig->render(
            'orders.twig',
            [
                'tariffs_entries' => $tariffs_list,
                'error' => $err,
                'orders' => $list,
                'orderedAt_from' => $filter['orderedAt']['from'] ?? '',
                'orderedAt_to' => $filter['orderedAt']['to'] ?? '',
                'tariff_id' => $filter['tariff_id'] ?? '',
                'name' => $filter['name'] ?? '',
                'type' => 'rides',
                'callback' => '/orders/ridesHistory',
                'title' => 'История поездок',
            ]
        );
    }

    public function getOrders()
    {
        $driver = new Driver()->getDriver($_SESSION['user_id']);
        [$filter, $err] = OrderValidator::validateFilter($_GET);
        $list = $this->model->getListFiltered($filter, null, $driver_id = $driver['id']);

        $tariffs_list = new Tariff()->getEntries();

        if ($_GET['type'] == 'pdf') {
            $this->generatePdf($list, 'history');
            $msg = "Отчет успешно составлен\n";
        } elseif ($_GET['type'] == 'excel') {
            $this->generatePdf($list, 'history');
            $msg = "Отчет успешно составлен\n";
        }

        echo $this->twig->render(
            'orders.twig',
            [
                'tariffs_entries' => $tariffs_list,
                'error' => $err,
                'orders' => $list,
                'orderedAt_from' => $filter['orderedAt']['from'] ?? '',
                'orderedAt_to' => $filter['orderedAt']['to'] ?? '',
                'tariff_id' => $filter['tariff_id'] ?? '',
                'type' => 'history',
                'callback' => '/orders/orderHistory',
                'title' => 'История заказов',
            ]
        );
    }

    private function generatePdf(array $data, string $reportType)
    {
        $pdf = new Pdf('L');
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
        exit;
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