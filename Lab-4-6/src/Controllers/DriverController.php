<?php
namespace src\Controllers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PDOException;
use src\Files\BaseUploader;
use src\Entities\Driver;
use src\Entities\User;
use src\Entities\Tariff;
use src\Validators\DriverValidator;
use Fawno\FPDF\FawnoFPDF;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use src\Validators\UserValidator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DriverController
{
    private EntityRepository $driver_rep;
    private EntityRepository $user_rep;
    private EntityRepository $tariff_rep;
    private Environment $twig;

    public function __construct(EntityManager $em)
    {
        $this->driver_rep = $em->getRepository(Driver::class);
        $this->user_rep = $em->getRepository(User::class);
        $this->tariff_rep = $em->getRepository(Tariff::class);
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($loader);
    }

    
    public function getEntries()
    {
        header('Content-type: application/json');
        $list = $this->driver_rep->createQueryBuilder('d')
        ->select([
            'u.name as name',
            'u.phone',
            'u.email',
            'd.intership',
            'd.carLicense',
            'd.carBrand',
            't.name as tariffName'
        ])
        ->join("d.user", 'u')
        ->leftJoin('d.tariff', 't')
        ->getQuery()
        ->getResult();
        echo json_encode($list);
    }

    public function index()
    {
        [$filter, $err] = DriverValidator::validateFilter($_GET);
        $list = $this->driver_rep->getListFiltered($filter);
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
            'drivers.twig',
            [
                'drivers' => $list,
                'message' => $msg,
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

    private function generatePdf(array $data)
    {

        function toWin1251(?string $text): ?string {
            if ($text === null){
                return null;
            }
            return iconv('UTF-8', 'windows-1251//IGNORE', $text);
        }

        define('FPDF_FONTPATH','../../public/fonts');
        $pdf = new FawnoFPDF();
        $pdf->AddPage('L');
        $fontname = 'Iosevka';
        

        $pdf->AddFont($fontname, '', 'IosevkaNerdFont_Regular.php', '/var/www/html/public/fonts/unifont');
        $pdf->AddFont($fontname, 'B', 'IosevkaNerdFont-Bold.php', '/var/www/html/public/fonts/unifont');

        // $pdf->SetFont('DejaVuSerif.ttf', 'B', 12);
        $pdf->SetFont($fontname, 'B', 12);
        $pdf->Cell(60, 10, toWin1251('Имя'), 1);
        $pdf->Cell(45, 10, toWin1251('Номер телефона'), 1);
        $pdf->Cell(60, 10, toWin1251('Почта'), 1);
        $pdf->Cell(15, 10, toWin1251('Стаж'), 1);
        $pdf->Cell(30, 10, toWin1251("Лицензионный номер"), 1);
        $pdf->Cell(60, 10, toWin1251('Марка машины'), 1);
        $pdf->Cell(60, 10, toWin1251('Название тариффа'), 1);

        $pdf->Ln();

        $pdf->SetFont($fontname, 'B', 12);

        foreach ($data as $row) {
            $pdf->Cell(60, 10, toWin1251($row['name']), 1);
            $pdf->Cell(45, 10, toWin1251($row['phone']), 1);
            $pdf->Cell(60, 10, toWin1251($row['email']), 1);
            $pdf->Cell(15, 10, toWin1251($row['intership']), 1);
            $pdf->Cell(30, 10, toWin1251($row['car_license']), 1);
            $pdf->Cell(60, 10, toWin1251($row['car_brand']), 1);
            $pdf->Cell(60, 10, toWin1251($row['tariff_name']), 1);
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
        $sheet->setCellValue($cells[$i++] . '1', 'Имя',);
        $sheet->setCellValue($cells[$i++] . '1', 'Номер телефона');
        $sheet->setCellValue($cells[$i++] . '1', 'Почта',);
        $sheet->setCellValue($cells[$i++] . '1', 'Стаж',);
        $sheet->setCellValue($cells[$i++] . '1', 'Лицензионный номер');
        $sheet->setCellValue($cells[$i++] . '1', 'Марка машины');
        $sheet->setCellValue($cells[$i++] . '1', 'Название тариффа');

        $rowIndex = 2;
        foreach ($data as $row) {
            $i = 0;
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['name']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['phone']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['email']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['intership']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['car_license']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['car_brand']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['tariff_name']);
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