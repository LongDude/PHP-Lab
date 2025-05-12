<?php
namespace src\Controllers;

use Doctrine\ORM\EntityManager;
use Fawno\FPDF\FawnoFPDF;
use Mgrn\Tfpdf\Pdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use src\Files\BaseUploader;
use src\Entities\Driver;
use src\Entities\Order;
use src\Entities\Tariff;
use src\Repository\DriverRepository;
use src\Repository\OrderRepository;
use src\Repository\TariffRepository;
use src\Validators\OrderValidator;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class OrderController
{
    private OrderRepository $orderRepository;
    private TariffRepository $tariffRepository;
    private DriverRepository $driverRepository;
    private Environment $twig;

    public function __construct(EntityManager $em)
    {
        $this->orderRepository = $em->getRepository(Order::class);
        $this->tariffRepository = $em->getRepository(Tariff::class);
        $this->driverRepository = $em->getRepository(Driver::class);
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($loader);
    }

    public function index()
    {
        [$filter, $err] = OrderValidator::validateFilter($_GET);
        $list = $this->orderRepository->getFilteredList($filter);
        $msg = '';

        $tariffs_list = $this->tariffRepository->findAll();
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

            $list = $this->orderRepository->getAvailableRides($filter);
            $avaliable_tariffs = $this->tariffRepository->findAll();

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

            $success = $this->orderRepository->addOrder(
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
        $list = $this->orderRepository->getFilteredList($filter, $_SESSION['user_id']);
        $tariffs_list = $this->tariffRepository->findAll();
        $msg = '';

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
                'type' => 'rides',
                'callback' => '/orders/ridesHistory',
                'title' => 'История поездок',
            ]
        );
    }

    public function getOrders()
    {
        $driver = $this->driverRepository->findOneBy(['user_id' => $_SESSION['user_id']]);
        [$filter, $err] = OrderValidator::validateFilter($_GET);
        $list = $this->orderRepository->getFilteredList($filter, null, $driver->getId());
        $msg = '';

        $tariffs_list = $this->tariffRepository->findAll();

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
                'type' => 'history',
                'callback' => '/orders/orderHistory',
                'title' => 'История заказов',
            ]
        );
    }

    private function generatePdf(array $data, string $reportType)
    {
        function toWin1251(?string $text): ?string
        {
            if ($text === null) {
                return null;
            }
            return iconv('UTF-8', 'windows-1251//IGNORE', $text);
        }

        define('FPDF_FONTPATH', '../../public/fonts');
        $pdf = new FawnoFPDF();
        $pdf->AddPage('L');
        $fontname = 'Iosevka';


        $pdf->AddFont($fontname, '', 'IosevkaNerdFont_Regular.php', '/var/www/html/public/fonts/unifont');
        $pdf->AddFont($fontname, 'B', 'IosevkaNerdFont-Bold.php', '/var/www/html/public/fonts/unifont');

        // $pdf->SetFont('DejaVuSerif.ttf', 'B', 12);
        $pdf->SetFont($fontname, 'B', 8);
        if ($reportType != 'history') {
            $pdf->Cell(20, 5, toWin1251('Телефон'), 1);
        }
        $pdf->Cell(30, 5, toWin1251('Начальная точка'), 1);
        $pdf->Cell(30, 5, toWin1251('Конечная точка'), 1);
        $pdf->Cell(20, 5, toWin1251('Расстояние'), 1);
        $pdf->Cell(40, 5, toWin1251('Время заказа'), 1);

        if ($reportType != 'rides') {
            $pdf->Cell(50, 5, toWin1251('Имя водителя'), 1);
        }
        if ($reportType == 'full') {
            $pdf->Cell(50, 5, toWin1251('Имя клиента'), 1);
        }

        $pdf->Cell(20, 5, toWin1251('Тарифф'), 1);
        $pdf->Cell(20, 5, toWin1251('Стоимость'), 1);

        $pdf->Ln();

        $pdf->SetFont($fontname, '', 8);
        foreach ($data as $row) {
            if ($reportType != 'history') {
                $pdf->Cell(20, 5, toWin1251($row['phone']), 1);
            }
            $pdf->Cell(30, 5, toWin1251($row['from_loc']), 1);
            $pdf->Cell(30, 5, toWin1251($row['dest_loc']), 1);
            $pdf->Cell(20, 5, toWin1251($row['distance']), 1);
            $pdf->Cell(40, 5, toWin1251($row['orderedAt']), 1);

            if ($reportType != 'rides') {
                $pdf->Cell(50, 5, toWin1251($row['driver_name']), 1);
            }
            if ($reportType == 'full') {
                $pdf->Cell(50, 5, toWin1251($row['user_name']), 1);
            }

            $pdf->Cell(20, 5, toWin1251($row['tariff_name']), 1);
            $pdf->Cell(20, 5, toWin1251($row['price']), 1);
            $pdf->Ln();
        }
        $pdf->Output('I', 'report.pdf');
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