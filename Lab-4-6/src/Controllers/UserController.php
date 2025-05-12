<?php

namespace src\Controllers;
use Doctrine\ORM\EntityManager;
use Fawno\FPDF\FawnoFPDF;
use src\Files\BaseUploader;
use src\Repository\UserRepository;
use src\Entities\User;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use src\Validators\UserValidator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class UserController{
    private UserRepository $userRepository;
    private Environment $twig;

    public function __construct(EntityManager $em)
    {
        $this->userRepository = $em->getRepository(User::class);
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

            $validationErrors = BaseUploader::validateCsv($file, User::FIELDS, new UserValidator());
            if ($validationErrors === "") {
                BaseUploader::saveCsv($file);
                if ($this->userRepository->importCsv(__DIR__ . "/../Files/Uploads/data.csv")) {
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

        $success = $this->userRepository->addUser(
            $_POST['name'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['password'],
            'client',
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

        $success = $this->userRepository->updateUser(
            $this->userRepository->find($_SESSION['user_id']),
            $_POST['name'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['password'],
            'client',
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
        $list = $this->userRepository->getFilteredList($filter);
        $msg = '';

        if ($err !== '') {
            $_SESSION['error'] = $err;
        }

        if (isset($_GET['type']) && $_GET['type'] === "pdf") {
            $this->generatePdf($list);
            $msg = "Отчет успешно составлен\n";
        } elseif (isset($_GET['type']) && $_GET['type'] == 'excel') {
            $this->generateExcel($list);
            $msg = "Отчет успешно составлен\n";
        }

        echo $this->twig->render(
            'users.twig',
            [
                'message' => $msg,
                'users' => $list,
                'name' => $filter["name"] ?? "",
                'phone' => $filter["phone"] ?? "",
                'email' => $filter["email"] ?? "",
            ]
        );
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
        $pdf->Cell(65, 10, toWin1251('Почта'), 1);
        $pdf->Cell(50, 10, toWin1251('Роль'), 1);

        $pdf->Ln();
        $pdf->SetFont($fontname, '', 12);
        foreach ($data as $row) {
            $pdf->Cell(60, 10, toWin1251($row['name']), 1);
            $pdf->Cell(45, 10, toWin1251($row['phone']), 1);
            $pdf->Cell(65, 10, toWin1251($row['email']), 1);
            $pdf->Cell(50, 10, toWin1251($row['role']), 1);
            $pdf->Ln();
        }
        $pdf->Output('I', 'report.pdf');
    }

    private function generateExcel(array $data)
    {
        $spreadsheet = new Spreadsheet();
        $cells = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        $sheet = $spreadsheet->getActiveSheet();

        $i = 0;
        $sheet->setCellValue($cells[$i++] . '1', 'Имя');
        $sheet->setCellValue($cells[$i++] . '1', 'Номер телефона');
        $sheet->setCellValue($cells[$i++] . '1', 'Почта');
        $sheet->setCellValue($cells[$i++] . '1', 'Роль');

        $rowIndex = 2;
        foreach ($data as $row) {
            $i = 0;
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['name']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['phone']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['email']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['role']);
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