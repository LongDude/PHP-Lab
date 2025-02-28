<?php
  session_start();
  header('Location: ./index.php');
  
  if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $car_registration = trim($_POST['car_registration'] ?? '');
    $tariffs = trim($_POST['tarifs'] ?? 0);    
    $correct = true;

    // Валидация имени
    if (!preg_match("/^[ а-яА-Яa-zA-Z]{1,50}$/", $name)){
      $correct = false;
      print($name);
      $_SESSION["inv_name"] = "Неккоректный формат имени";
    } else {unset($_SESSION["inv_name"]);}

    // Валидация номера телефона
    if (!preg_match("/\+7 \([0-9]{3}\) [0-9]{3}-[0-9]{2}-[0-9]{2}/", $phone)){
      $correct = false;
      $_SESSION["inv_phone"] = "Неккоректный формат номера";
    } else {unset($_SESSION["inv_phone"]);}

    // Валидация регистрационного номера машины
    if (!preg_match("/^[a-zA-Z0-9]{4,8}$/", $car_registration)){
      $correct = false;
      $_SESSION["inv_registration"] = "Неккоректный формат регистрационного номера";
    } else {unset($_SESSION["inv_registration"]);}

    // Валидация тарифа
    if (!(preg_match("/^\d+$/", $tariffs) && 0 < $tariffs && $tariffs <= 5000)){
      $correct = false;
      $_SESSION["inv_tariffs"] = "Неккоректный тариф";
    } else {unset($_SESSION["inv_tariffs"]);}

    if ($correct){
      $csvFile = 'data.csv';
      $dataRow = [$name, $phone, $car_registration, $tariffs];

      if (($file = fopen($csvFile, 'a')) !== false) {
        fputcsv($file, $dataRow);
        fclose($file);
        $message = 'Данные успешно проданы пендосам';
      } else {
        $message = 'Ошибка при сохранении';
      }
      $_SESSION['message'] = $message;
    }
    else {
      $_SESSION['message'] = "Некоторые поля неккоректно заполнены";
    }
  }
?>
