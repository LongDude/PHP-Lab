<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css">
    <script src="/js/core.js" defer></script>
    <script src="/js/validators/TariffValidator.js" defer></script>
    <title>Такси сервис "Туда-Сюда"</title>
</head>
<body>
    <script>
        window.onload = () => {
            let message = <? echo json_encode($_SESSION['message'] ?? '') ?>
            let err = <? echo json_encode($_SESSION['error'] ?? '') ?>

            if (message){
                alert(message);
            }

            if (err){
                console.error(err);
            }

            <?php
            $_SESSION['message'] = '';
            $_SESSION['error'] = '';
            ?>
        }
    </script>
    <main class="main-panel">
        <h1>Добавление новых тарифов</h1>
        <form action="add" method="post" name="form" id="form" enctype="multipart/form-data">
            <div class="input-group">
                <label for="name">Название тарифа</label>
                <input type="text" name="name" id="name" placeholder="Базовый">
                <!-- <label for="name" class="input-info"></label> -->
            </div>
            <div class="input-group">
                <label for="base_price">Базовая стоимость</label>
                <input type="text" name="base_price" id="base_price" placeholder="0.0" min="0">
                <!-- <label for="base_price" class="input-info"></label> -->
            </div>
            <div class="input-group">
                <label for="base_dist">Расстояние, включенное в базовый тариф</label>
                <input type="text" name="base_dist" id="base_dist" placeholder="0.0" min="0">
                <!-- <label for="base_dist" class="input-info"></label> -->
            </div>
            <div class="input-group">
                <label for="base_time">Время, включенное в базовый тариф</label>
                <input type="text" name="base_time" id="base_time" placeholder="0.0" min="0">
                <!-- <label for="base_time" class="input-info"></label> -->
            </div>
            <div class="input-group">
                <label for="dist_cost">Цена за 1 км</label>
                <input type="text" name="dist_cost" id="dist_cost" placeholder="0.0" min="0">
                <!-- <label for="dist_cost" class="input-info"></label> -->
            </div>
            <div class="input-group">
                <label for="time_cost">Цена за 1 мин</label>
                <input type="text" name="time_cost" id="time_cost" placeholder="0.0" min="0">
                <!-- <label for="time_cost" class="input-info"></label> -->
            </div>
            <input type="submit" value="Отправить" id="submit">
            <div class="input-group">
                <label for="csv-file">Отправить CSV файл</label>
                <input type="file" name="csv-file" id="csv-file" accept=".csv">
                <label for="csv-file" hidden></label>
            </div>
        </form>
    </main>
</body>
</html>