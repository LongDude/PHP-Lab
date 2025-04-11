<?php

namespace src\Controllers;
use PDOException;
use src\Files\BaseUploader;
use src\Models\User;
use Fawno\FPDF\FawnoFPDF;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use src\Validators\UserValidator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class UserController{
    private User $user_model;
    private Environment $twig;

    public function __construct()
    {
        $this->user_model = new User();
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

            $validationErrors = BaseUploader::validateCsv($file, User::fields, new UserValidator());
            if ($validationErrors === "") {
                BaseUploader::saveCsv($file);
                if ($this->user_model->importCsv(__DIR__ . "/../Files/Uploads/data.csv")) {
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

        $success = $this->user_model->addUser(
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

        $success = $this->user_model->updateUser(
            $_SESSION['user_id'],
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
        $list = $this->user_model->getListFiltered($filter);

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
                'users' => $list,
                'name' => $filter["name"] ?? "",
                'phone' => $filter["phone"] ?? "",
                'email' => $filter["email"] ?? "",
            ]
        );
    }

    private function generatePdf(array $data)
    {
        $pdf = new FawnoFPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(20, 10, 'Телефон клиента', 1);
        $pdf->Cell(20, 10, 'Начальная точка', 1);
        $pdf->Cell(20, 10, 'Конечная точка', 1);
        $pdf->Cell(20, 10, 'Расстояние', 1);
        $pdf->Cell(20, 10, 'Время заказа', 1);
        $pdf->Cell(20, 10, 'Имя водителя', 1);
        $pdf->Cell(20, 10, 'Имя клиента', 1);
        $pdf->Cell(20, 10, 'Тарифф', 1);
        $pdf->Cell(20, 10, 'Стоимость', 1);

        $pdf->Ln();

        $pdf->SetFont('Arial', '', 12);
        foreach ($data as $row) {
            $pdf->Cell(20, 10, $row['phone'], 1);
            $pdf->Cell(20, 10, $row['from_loc'], 1);
            $pdf->Cell(20, 10, $row['dest_loc'], 1);
            $pdf->Cell(20, 10, $row['distance'], 1);
            $pdf->Cell(20, 10, $row['orderedAt'], 1);
            $pdf->Cell(20, 10, $row['driver_name'], 1);
            $pdf->Cell(20, 10, $row['user_name'], 1);
            $pdf->Cell(20, 10, $row['tariff_name'], 1);
            $pdf->Cell(20, 10, $row['price'], 1);
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
        $sheet->setCellValue($cells[$i++] . '1', 'Номер телефона');
        $sheet->setCellValue($cells[$i++] . '1', 'Начальная точка');
        $sheet->setCellValue($cells[$i++] . '1', 'Конечная точка');
        $sheet->setCellValue($cells[$i++] . '1', 'Расстояние');
        $sheet->setCellValue($cells[$i++] . '1', 'Время заказа');
        $sheet->setCellValue($cells[$i++] . '1', 'Имя водителя');
        $sheet->setCellValue($cells[$i++] . '1', 'Имя клиента');
        $sheet->setCellValue($cells[$i++] . '1', 'Тариф');
        $sheet->setCellValue($cells[$i++] . '1', 'Стоимость');

        $rowIndex = 2;
        foreach ($data as $row) {
            $i = 0;
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['phone']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['from_loc']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['dest_loc']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['distance']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['orderedAt']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['driver_name']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['user_name']);
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