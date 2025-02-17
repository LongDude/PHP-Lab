<?php
  if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $car_registration = trim($_POST['car_registration'] ?? '');
    $tariffs = trim($_POST['tarifs'] ?? '');    

    $csvFile = 'data.csv';
    $dataRow = [$name, $phone, $car_registration, $tariffs];

    if (($file = fopen($csvFile, 'a')) !== false) {
      fputcsv($file, $dataRow);
      fclose($file);
      $message = 'Данные успешно проданы пендосам';
    } else {
      $message = 'Ошибка при сохранении';
    }
    echo $message;
  }
?>
