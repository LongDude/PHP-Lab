<?php

use src\Models\Driver;
use src\Models\Tariff;
session_start();
# TODO поддержка перекрестного списка водителей по тарифу
# TODO интеграция интерактивной карты
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css">
    <script src="/js/core.js" defer></script>
    <script src="/js/validators/OrderValidator.js" defer></script>
    <title>Такси сервис "Туда-Сюда"</title>
</head>

<body>
    <script>
        window.onload = () => {
            let message = <?= json_encode($_SESSION['message'] ?? '') ?>;
            let err = <?= json_encode($_SESSION['error'] ?? '') ?>;

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
        <h1>Добавление заказа</h1>
        <form action="add" method="post" name="form" id="form" enctype="multipart/form-data">
            <div class="input-group">
                <label for="phone">Контактный номер клиента:</label>
                <input type="text" name="phone" id="phone" placeholder="+7 (123) 456-78-90">
                <!-- <label for="phone" class="input-info"></label> -->
            </div>
            <div class="input-group">
                <label for="from_loc">TEMP: Начало маршрута</label>
                <input type="text" name="from_loc" id="from_loc" placeholder="0.000000;0.000000">
                <label for="from_loc" class="input-info">от -360.000000 до 360.000000</label>
            </div>
            <div class="input-group">
                <label for="dest_loc">TEMP: Конец маршрута</label>
                <input type="text" name="dest_loc" id="dest_loc" placeholder="0.000000;0.000000">
                <label for="dest_loc" class="input-info">от -360.000000 до 360.000000</label>
            </div>
            <div class="input-group">
                <label for="distance">TEMP: Расстояние</label>
                <input type="text" name="distance" id="distance" placeholder="0.0" min="0">
                <!-- <label for="distance" class="input-info"></label> -->
            </div>
            <div class="input-group">
                <label for="tariff_id">TEMP: Тариф</label>
                <select name="tariff_id" id="tariff_id">
                    <?php foreach (new Tariff()->getEntries() as $value): ?>
                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="tariff_id"></label>
            </div>
            <div class="input-group">
                <label for="driver_id">TEMP: Водитель</label>
                <select name="driver_id" id="driver_id">
                    <?php foreach (new Driver()->getEntries() as $value): ?>
                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="driver_id"></label>
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