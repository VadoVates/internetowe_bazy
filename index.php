<?php
    try {
        $pdo = new PDO('mysql:host=' . $host . ';dbname=' . $dbname . ';port=' . $port, $username, $password);
        echo 'Połączenie nawiązane! <br />';
    } catch (PDOException $e) {
        /* Wybrałem zapis błędu do pliku error_log.log. Trzeba przypilnować aby ten plik istniał
         oraz aby była możliwość zapisu do niego (uprawnienia) */
        echo 'Połączenie z bazą danych nie zostało utworzone. <br />';
        $errormsg= "[" . date('Y-m-d H:i:s') . "] " . (string)$e . PHP_EOL;
        error_log($errormsg, 3, 'error_log.log');
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Hello World - Internetowe Bazy Danych</title>
    </head>
    <body>
        <?php echo '<p>Hello World!</p>'; ?>
    </body>
</html>