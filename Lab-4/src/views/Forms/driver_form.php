<?php

use src\Models\Tariff;
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css">
    <script src="/js/core.js" defer></script>
    <script src="/js/validators/DriverValidator.js" defer></script>
    <title>Такси сервис "Туда-Сюда"</title>
</head>

<body>
    <script>
        window.onload = () => {
            let message = <?= json_encode($_SESSION['message'] ?? '') ?>;
            let err = <?= json_encode($_SESSION['error'] ?? '') ?>

            if (message) {
                alert(message);
            }

            if (err) {
                console.error(err);
            }
            <?php
            $_SESSION['message'] = '';
            $_SESSION['error'] = '';
            ?>
        }
    </script>
    <main class="main-panel">
        <h1>Регистрация водителей</h1>
        <form action="add" method="post" name="form" id="form" enctype="multipart/form-data">
            <div class="input-group">
                <label for="name">Имя:</label>
                <input type="text" name="name" id="name" placeholder="Василий Пупкин">
                <!-- <label for="name" class="input-info"></label> -->
            </div>
            <div class="input-group">
                <label for="phone">Контактный номер:</label>
                <input type="text" name="phone" id="phone" placeholder="+7 (123) 456-78-90">
                <!-- <label for="phone" class="input-info"></label> -->
            </div>
            <div class="input-group">
                <label for="email">Почтовый адрес:</label>
                <input type="email" name="email" id="email" placeholder="abc@xyz.com">
                <!-- <label for="email" class="input-info"></label> -->
            </div>
            <div class="input-group">
                <label for="intership">Стаж:</label>
                <input type="number" name="intership" id="intership" min='0' max="100" placeholder="0">
                <label for="intership" class="input-info">Число от 0 до 100</label>
            </div>
            <div class="input-group">
                <label for="car_license">Номер машины:</label>
                <input type="text" name="car_license" id="car_license" placeholder="XXXXXX XXXX">
                <label for="car_license" class="input-info">Лицензионный номер</label>
            </div>
            <div class="input-group">
                <label for="car_brand">Марка машины:</label>
                <input type="text" name="car_brand" id="car_brand">
                <label for="car_brand" class="input-info">До 50 символов</label>
            </div>
            <div class="input-group">
                <label for="tariff_id">Тариф:</label>
                <select name="tariff_id" id="tariff_id">
                    <?php foreach (new Tariff()->getEntries() as $value): ?>
                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <!-- <label for="tariff_id"></label> -->
            </div>
            <input type="submit" value="Отправить" id="submit">
            <div class="input-group">
                <label for="csv-file">Отправить CSV файл</label>
                <input type="file" name="csv-file" id="csv-file" accept=".csv">
                <label for="csv-file" hidden>Ошибка записи</label>
            </div>
        </form>
    </main>
</body>
</html>