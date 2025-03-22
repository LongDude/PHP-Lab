<?php session_start() ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Такси-сервис "Туда-Сюда"</title>

<body>
  <main>
    <h1>"Туда-Сюда" и уже приехали!</h1>
    <form>
      <div>
        <label for="name">Имя:</label>
        <input type='text' name="name" id="name">
        <label for="name" class="input-info">4-50 символов кириллицей/латиницей</label>
      </div>

      <div>
        <label for="sex">Пол:</label>
        <select name="sex" id="sex">
          <option value="М">М</option>
          <option value="Ж">Ж</option>
          <option value="every-day">Пиво "Каждый день"</option>
          <option value="apache">Боевой вертолёт</option>
        </select>
      </div>

      <div>
        <label for="phone">Контакстный номер:</label>
        <input type="text" name="phone" placeholder="+7 (999) 999-99-99" id="phone">
      </div>

      <div>
        <label for="email">Почтовый адрес:</label>
        <input type="email" name="email" id="email" placeholder="abc@xyz.com">
      </div>

      <div>
        <label for="intership">Стаж:</label>
        <input type="number" name="intership" id="intership" min="1" max="90">
      </div>

      <div>
        <label for="car_registration">Номера машины:</label>
        <input type="text" name="car_registration" id="car_registration">
        <label for="car_registration" class="input-info">4-8 символов латиницей/арабских цифр, без пробелов</label>
      </div>

      <div>
        <label for="tarifs"> Тариф:</label>
        <input type="text" name="tarifs" min="100" max="5000" id="tarifs">
        <label for="tarifs" class="input-info">От 100 до 5000</label>
      </div>

      <input type="submit" value="Отправить" id="submit">
    </form>
  </main>
  <script src="validator.js"></script>

  <body>

</html>