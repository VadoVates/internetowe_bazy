<!DOCTYPE html>
<html>

<!-- Autor: Marek Górski -->
<!-- nr indeksu: 155647 -->
<!-- grupa D1 -->
<!-- rok akademicki 2024/2025 -->
<!-- semestr V -->

    <head>
        <title>Internetowe Bazy Danych</title>
        <style>
            table, th, td {
                border: 1px solid black;
                border-collapse: collapse;
            }
        </style>
    </head>
    <body>
        <h1>Wyświetl użytkowników</h1>
        <h5>Delete - powoduje usunięcie użytkownika oraz uruchomienie wyzwalacza po usunięciu</h5>
        <h5>Edit - po edycki użytkownika zostanie uruchomiony wyzwalacz</h5>
        <table style="width:1000px">
            <tr><th>#</th><th>Name</th><th>Email</th><th>Action</th></tr>
            <?php
                $host = 'localhost';
                $dbname = 'test';
                $username = 'int_baz';
                $password = '1nt3rn3t0w3_b4zy';
                $port = 3306;


                try {
                    $pdo = new PDO('mysql:host=' . $host . ';dbname=' . $dbname . ';port=' . $port, $username, $password);
                    
                    $sql = 'SELECT * FROM `subscribers`';
                    $stmt = $pdo->query($sql);

                    foreach($stmt as $row) {
                        echo '<tr>';
                        echo '<td>'. htmlspecialchars($row['id']) .'</td>';
                        echo '<td>'. htmlspecialchars($row['fname']) .'</td>';
                        echo '<td>'. htmlspecialchars($row['email']) .'</td>';
                        echo '<td>';
                        echo '<a href="subscriber_del.php?id=' . urlencode($row['id']) . '">Delete</a> | ';
                        echo '<a href="subscriber_edit.php?id=' . urlencode($row['id']) . '">Edit</a>';
                        echo '</tr>';
                    }
                } catch (PDOException $e) {
                    echo "Wystąpił błąd";
                    $errormsg= "[" . date('Y-m-d H:i:s') . "] " . (string)$e . PHP_EOL;
                    error_log($errormsg, 3, 'error_log.log');
                }
            ?>
        </table>
    </body>
</html>