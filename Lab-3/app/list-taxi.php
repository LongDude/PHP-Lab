<?php
    require "db.php";

    $stmt = $pdo->query("SELECT * FROM taxi_registration");
    $taxis = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // var_dump($taxi);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список клоунов</title>
    <style>
        table {
            width: 50%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        th, td {
            padding: 10px;
            border: 1px solid black;
            text-align: center;
        }
        th {
            background-color: greenyellow;
        }
    </style>
</head>
<body>
    <h2>Список водителей</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Phone</th>
            <th>Taxi registration</th>
            <th>Tariffs</th>
        </tr>
        <?php foreach($taxis as $taxi): ?>
        <tr>
            <td>
                <?= $taxi['id']?>
            </td>
            <td>
                <?= $taxi['name']?>
            </td>
            <td>
                <?= $taxi['phone_number']?>
            </td>
            <td>
                <?= $taxi['registration_id']?>
            </td>
            <td>
                <?= $taxi['tariff']?>
            </td>
        </tr>        
        <?php endforeach; ?>
    </table>
</body>
</html>