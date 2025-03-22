<?php
session_start();
header('Content-type: application/json');

require "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $sex = trim($_POST['sex'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $intership = trim($_POST['intership'] ?? '');
  $car_registration = trim($_POST['car_registration'] ?? '');
  $tariffs = trim($_POST['tarifs'] ?? 0);
  $correct = true;
  $err = "";

  // Валидация имени
  if (!preg_match("/^[a-zA-Zа-яА-Я][a-zA-Zа-яА-Я ]{3,49}\$/ui", $name)) {
    $err .= "INVALID NAME\n";
    $correct = false;
  }
  // Валидация номера телефона
  if (!preg_match("/\+7 \([0-9]{3}\) [0-9]{3}-[0-9]{2}-[0-9]{2}/", $phone)) {
    $err .= "INVALID PHONE\n";
    $correct = false;
  }

  // Валидация почты
  if (!preg_match("/^[a-zA-Z]\S*@[a-zA-Z]+\.[a-zA-Z]+$/", $email)) {
    $err .= "INVALID EMAIL\n";
    $correct = false;
  }

  // Валидация пола
  if (strlen($sex) == 0 || !in_array($sex, array('every-day', 'apache', 'М', 'Ж'))){
    $err .= "NO SEX\n";
    $correct = false;
  }

  // Валидация стажа
  if (strlen($intership) == 0 || $intership <= 0 || $intership >= 100){
    $err .= "INVALID INTERSHIP\n";
    $correct = false;
  }

  // Валидация регистрационного номера машины
  if (!preg_match("/^[а-яA-Z0-9]{4,8}[ -][а-яЫA-Z0-9]{2,4}$/ui", $car_registration)) {
    $err .= "INVALID LICENSE\n";
    $correct = false;
  }

  // Валидация тарифа
  if (!(preg_match("/^\d+$/", $tariffs) && 100 <= $tariffs && $tariffs <= 5000)) {
    $err .= "INVALID TARIFF\n";
    $correct = false;
  }

  if ($correct) {
    $csvFile = 'data.csv';
    $dataRow = [$name, $phone, $email, $sex, $intership, $car_registration, $tariffs];


    $stmt = $pdo->prepare("INSERT INTO drivers (name, phone, email, sex, intership, car_registration, tariffs) VALUES (?,?,?,?,?,?,?)");
    if($stmt->execute($dataRow)){
      $msg = "Данные успешно проданы пендосам";
      http_response_code(200);
    } else {
      http_response_code(500);
      $msg = "Ошибка";
      $err = $pdo->errorInfo();
    }
    
  } else {
    http_response_code(400);
    $msg = "Неккоректно заполнены поля";
  }
  echo json_encode(array('err' => $err, 'msg' => $msg));
}
?>