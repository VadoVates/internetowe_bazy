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
        <h1>Widoki z zadania trzeciego</h1>
            <?php
                $host = 'localhost';
                $dbname = 'test';
                $username = 'int_baz';
                $password = '1nt3rn3t0w3_b4zy';
                $port = 3306;

                try {
                    $pdo = new PDO('mysql:host=' . $host . ';dbname=' . $dbname . ';port=' . $port, $username, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $sql = 'SHOW TABLES LIKE \'%view\'';
                    $views = $pdo->prepare($sql);
                    $views->execute();

                    foreach($views as $view) {
                        echo '<h4>Widok wyświetlający '. htmlspecialchars($view[0]) .'</h4>';
                        echo '<table style="width:1000px">';
                        echo '<thead>';
                        echo '<tr>';
                        $sql = 'SHOW COLUMNS FROM ' . $view[0];
                        $columns = $pdo->query($sql);                        
                        foreach($columns as $column) {
                            echo '<th>';
                            echo htmlspecialchars($column[0]);
                            echo '</th>';
                        }
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        $sql = 'SELECT * FROM ' . $view[0];
                        //PDO::FETCH_ASSOC zwraca tylko treść, bez id
                        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                        foreach($rows as $row) {
                            echo '<tr>';
                            foreach ($row as $single_data) {
                                echo '<td>';
                                echo htmlspecialchars($single_data);
                                echo '</td>';
                            }
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                    }
                } catch (PDOException $e) {
                    echo "Wystąpił błąd";
                    $errormsg= "[" . date('Y-m-d H:i:s') . "] " . (string)$e . PHP_EOL;
                    error_log($errormsg, 3, 'error_log.log');
                }
            ?>
        <p><a href="index.php">Dodaj użytkownika</a></p>
        <p><a href="viewsubscribers.php">Lista subskrybentów</a></p>
        <p><a href="custom_view.php">Tworzenie widoku</a></p>
    </body>
</html>