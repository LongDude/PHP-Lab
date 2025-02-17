<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"> 
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Такси-сервис "Туда-Сюда"</title>
<body>
  <h1>"Туда-Сюда" и уже приехали!</h1>
  
  <?php if (!empty($message)): ?>
    <p><?php echo $message; ?></p>
  <?php endif; ?>

  <form action="form.php" method="POST">
    <label for="name">Имя:</label>
    <input type='text' name="name" id="name">
    <br>
    <label for="phone">Контакстный номер:</label>
    <input type="text" name="phone" id="phone" pattern="+[0-9]{1,11}">
    <br>
    <label for="car_registration">Номера машины:</label>
    <input type="text" name="car_registration" pattern="\S{1,6}" id="tarifs"> 
    <br>
    <label for="tarifs"> Тариф:</label>
    <input type="text" name="tarifs" pattern="[0-9]{1,4}" id="tarifs"> 
    <br>
    <input type="submit" value="Отправить">
  </form>  
<body>
</html>
