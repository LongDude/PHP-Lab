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

    
    <?php if (!empty($message)): ?>
      <p><?php echo $message; ?></p>
      <?php endif; ?>
      
      <form action="form.php" method="POST">
        <label for="name">Имя:</label>
        <input type='text' name="name" id="name">
        <label for="phone">Контакстный номер:</label>
        <input type="text" name="phone" id="phone" pattern="+[0-9]{1,11}">
        <label for="car_registration">Номера машины:</label>
        <input type="text" name="car_registration" pattern="\S{1,6}" id="tarifs"> 
        <label for="tarifs"> Тариф:</label>
        <input type="text" name="tarifs" pattern="[0-9]{1,4}" id="tarifs"> 
        <input type="submit" value="Отправить" id="submit">
      </form>  
    </main>
<body>
</html>
