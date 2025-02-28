<?php session_start()?>

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

      <?php         
        if (isset($_SESSION["message"])) {
          $msg = $_SESSION["message"]; 
          echo "<script type='text/javascript'>alert('$msg');</script>";
          unset($_SESSION["message"]);
        }
      ?>
      
      <form action="form.php" method="POST">
        <div>
          <label for="name">Имя:</label>
          <input type='text' name="name" id="name" <?php if (isset($_SESSION['inv_name'])) echo 'class="invalid"';?>>
          <label for="name" class="input-info">1-50 кириллических символов</label>
        </div>

        <div>
          <label for="phone">Контакстный номер:</label>
          <input type="text" name="phone" placeholder="+7 (999) 999-99-99" id="phone" <?php if (isset($_SESSION['inv_phone'])) echo 'class="invalid"';?>>
        </div>
        
        <div>
          <label for="car_registration">Номера машины:</label>
          <input type="text" name="car_registration" id="car-registration" <?php if (isset($_SESSION['inv_registration'])) echo 'class="invalid"';?>> 
          <label for="phone" class="input-info">4-8 символов латиницей/арабских цифр, без пробелов</label>
        </div>
        
        <div>
          <label for="tarifs"> Тариф:</label>
          <input type="number" name="tarifs" min="100" max="5000" id="tarifs" <?php if (isset($_SESSION['inv_tariffs'])) echo 'class="invalid"';?>> 
          <label for="phone" class="input-info">От 100 до 5000</label>
        </div>

        <input type="submit" value="Отправить" id="submit">
      </form>  
    </main>
    <script src="validator.js"></script>
<body>
</html>
