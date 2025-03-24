<?php

use Src\Models\Driver;
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="/js/core.js" defer></script>
    <script src="/js/validators/DriverValidator.js" defer></script>
    <title>Такси сервис "Туда-Сюда"</title>
</head>
<body>
    <script>
        window.onload = () => {
            let message = <?= json_encode($_SESSION['message'] ?? ''); ?>
            let err = <?= json_encode($_SESSION['error']) ?>

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
        <h1>Регистрация водителей</h1>
        <form action="add" method="post" name="form" id="form" enctype="multipart/form-data">
            <div class="input-group">
                <label for="name"></label>
                <input type="text" name="name" id="name">
                <label for="name"></label>
            </div>
            <div class="input-group">
                <label for="phone"></label>
                <input type="text" name="phone" id="phone">
                <label for="phone"></label>
            </div>
            <div class="input-group">
                <label for="email"></label>
                <input type="text" name="email" id="email">
                <label for="email"></label>
            </div>
            <div class="input-group">
                <label for="intership"></label>
                <input type="text" name="intership" id="intership">
                <label for="intership"></label>
            </div>
            <div class="input-group">
                <label for="car_license"></label>
                <input type="text" name="car_license" id="car_license">
                <label for="car_license"></label>
            </div>
            <div class="input-group">
                <label for="car_brand"></label>
                <input type="text" name="car_brand" id="car_brand">
                <label for="car_brand"></label>
            </div>
            <div class="input-group">
                <label for="tariff_id"></label>
                <select name="tariff_id" id="tariff_id">
                    <?php foreach (new Driver()->getEntries() as $value): ?>
                        <option value="<?=$value['id']?>"><?=$value['name']?></option>
                    <?php endforeach; ?>
                </select>
                <label for="tariff_id"></label>
            </div>
        </form>
    </main>
</body>
</html>