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
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // GET -> załadowanie strony, zmienna $table nieustawiona
                    if ($_SERVER['REQUEST_METHOD'] === 'GET' || !isset($_POST ['table'])) {
                        echo '<form method="POST">';
                        echo '<p>Wybierz tablicę, z której chciałbyś wyświetlić rekordy:</p>';

                        $sql = 'SHOW TABLES';
                        $tables = $pdo->query($sql);
                        echo '<select id="table" name="table" required>';
                        foreach($tables as $table) {
                            echo '<option value="' . htmlspecialchars($table[0]) . '">';
                            echo htmlspecialchars($table[0]);
                            echo '</option>' ;
                        }
                        echo '</select>';
                        echo '<br />';
                        echo '<button type="submit">Zatwierdź</button>';
                        echo '</form>';
                    }

                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table'])) {
                        $selected_table = htmlspecialchars($_POST['table']);
                        echo '<h3>Wybrana tabela: ' . $selected_table . '</h3>';

                        $sql = 'SHOW COLUMNS FROM ' . $selected_table;
                        $columns = $pdo->query($sql);
                        echo '<form method="POST">';
                        echo '<p>Zaznacz które kolumny chcesz wyświetlić</p>';
                        // przerzucenie do kolejnego POST-a nazwy tabeli
                        echo '<input type="hidden" name="table" value"' . $selected_table . '">';
                        
                        foreach ($columns as $column) {
                            echo '<label>';
                            echo '<input type="checkbox" name="columns[]" value="' . htmlspecialchars($column[0]) . '">' . htmlspecialchars($column[0]);
                            echo '</label><br />';
                        }
                        echo '<button type="submit">Zatwierdź</button>';
                        echo '</form>';
                    }
                } catch (PDOException $e) {
                    echo '</form>';
                    echo 'Wystąpił błąd';
                    $errormsg= '[' . date('Y-m-d H:i:s') . '] ' . (string)$e . PHP_EOL;
                    error_log($errormsg, 3, 'error_log.log');
                }
            ?>
        <p><a href="index.php">Dodaj użytkownika</a></p>
        <p><a href="viewsubscribers.php">Lista subskrybentów</a></p>
        <p><a href="views.php">Widoki</a></p>
        <p><a href="custom_view.php">Tworzenie widoku</a></p>
    </body>
</html>