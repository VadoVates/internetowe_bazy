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
        <h1>Definiowanie własnego widoku tabeli</h1>
            <?php
                $host = 'localhost';
                $dbname = 'test';
                $username = 'int_baz';
                $password = '1nt3rn3t0w3_b4zy';
                $port = 3306;

                try {
                    $pdo = new PDO('mysql:host=' . $host . ';dbname=' . $dbname . ';port=' . $port, $username, $password);

                    $sql = '';
                    $stmt = $pdo->query($sql);
                    foreach($stmt as $row) {

                    }
                } catch (PDOException $e) {
                    echo "Wystąpił błąd";
                    $errormsg= "[" . date('Y-m-d H:i:s') . "] " . (string)$e . PHP_EOL;
                    error_log($errormsg, 3, 'error_log.log');
                }
            ?>
        <p><a href="index.php">Dodaj użytkownika</a></p>
        <p><a href="viewsubscribers.php">Lista subskrybentów</a></p>
        <p><a href="views.php">Widoki</a></p>
        <p><a href="custom_view.php">Tworzenie widoku</a></p>
    </body>
</html>