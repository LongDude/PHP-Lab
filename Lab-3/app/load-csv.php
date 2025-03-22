<?php
header('Content-type: application/json');
require "db.php";
// error_reporting(0);
$csvFile = 'data.csv';
if (($file = fopen($csvFile, 'r')) !== false) {

    $rows = 0;
    $err = array();
    while (($row = fgetcsv($file, 1000, ',', escape: "\n")) !== false) {
        $rows++;

        if (count($row) !== 7) {
            http_response_code(400);
            $err[] = "Строка $rows неккоректного формата;";
            break;
        }

        list($name, $sex, $phone, $email, $intership, $car_registration, $tariffs) = array_map('trim', $row);
        $correct = true;

        // Валидация имени
        if (!preg_match("/^[a-zA-Zа-яА-Я][a-zA-Zа-яА-Я ]{3,49}\$/ui", $name)) {
            $err[] = "INVALID NAME;";
            $correct = false;
        }
        // Валидация номера телефона
        if (!preg_match("/\+[7-8] \([0-9]{3}\) [0-9]{3}-[0-9]{2}-[0-9]{2}/", $phone)) {
            $err[] = "INVALID PHONE;";
            $correct = false;
        }

        // Валидация почты
        if (!preg_match("/^[a-zA-Z]\S*@[a-zA-Z]+\.[a-zA-Z]+$/", $email)) {
            $err[] = "INVALID EMAIL;";
            $correct = false;
        }

        // Валидация пола
        if (strlen($sex) == 0 || !in_array($sex, array('every-day', 'apache', 'М', 'Ж'))) {
            $err[] = "NO SEX;";
            $correct = false;
        }

        // Валидация стажа
        if (strlen($intership) == 0 || $intership <= 0 || $intership >= 100) {
            $err[] = "INVALID INTERSHIP;";
            $correct = false;
        }

        // Валидация регистрационного номера машины
        if (!preg_match("/^[а-яA-Z0-9]{5,7}[ -][а-яЫA-Z0-9]{2,4}$/ui", $car_registration)) {
            $err[] = "INVALID LICENSE:$car_registration";
            $correct = false;
        }

        // Валидация тарифа
        if (!(preg_match("/^\d+$/", $tariffs) && 100 <= $tariffs && $tariffs <= 5000)) {
            $err[] = "INVALID TARIFF;";
            $correct = false;
        }

        if ($correct) {
            $dataRow = [$name, $phone, $email, $sex, $intership, $car_registration, $tariffs];
            $stmt = $pdo->prepare("INSERT INTO drivers (name, phone, email, sex, intership, car_registration, tariffs) VALUES (?,?,?,?,?,?,?)");
            if (!$stmt->execute($dataRow)) {
                http_response_code(500);
                $msg = "Ошибка";
                $err[] = $pdo->errorInfo();
                break;
            }
        } else {
            http_response_code(400);
            $err[] = "Format error in line $rows;";
            break;
        }
    }


    fclose($file);
    echo json_encode(array(
        'msg' => $msg ?? '',
        'err' => $err ?? ''
    ));

} else {
    http_response_code(500);
    echo json_encode("Ошибка записи");

}
?>