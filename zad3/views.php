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

                    $sql = 'SELECT * FROM `user_creation_view`';
                    $stmt = $pdo->query($sql);
                    echo '<h4>ad.1. Widok wyświetlający nazwę użytkowników oraz datę ich dodania</h4>';
                    echo '<table style="width:1000px">';
                    echo '<tr><th>ID</th><th>Name</th><th>Creation date</th></tr>';
                    foreach($stmt as $row) {
                        echo '<tr>';
                        echo '<td>'. htmlspecialchars($row['user_id']) .'</td>';
                        echo '<td>'. htmlspecialchars($row['subscriber_name']) .'</td>';
                        echo '<td>'. htmlspecialchars($row['creation_date']) .'</td>';
                        echo '</tr>';
                    }
                    echo '</table>';                    

                    $sql = 'SELECT * FROM `user_deletion_view`';
                    $stmt = $pdo->query($sql);
                    echo '<h4>ad.2. Widok wyświetlający nazwę użytkowników oraz datę ich usunięcia</h4>';
                    echo '<table style="width:1000px">';
                    echo '<tr><th>ID</th><th>Name</th><th>Deletion date</th></tr>';
                    foreach($stmt as $row) {
                        echo '<tr>';
                        echo '<td>'. htmlspecialchars($row['user_id']) .'</td>';
                        echo '<td>'. htmlspecialchars($row['subscriber_name']) .'</td>';
                        echo '<td>'. htmlspecialchars($row['deletion_date']) .'</td>';
                        echo '</tr>';
                    }
                    echo '</table>';                    
                    
                    $sql = 'SELECT * FROM `user_edit_view`';
                    $stmt = $pdo->query($sql);
                    echo '<h4>ad.3. Widok wyświetlający nazwę użytkowników oraz datę ich edycji</h4>';
                    echo '<table style="width:1000px">';
                    echo '<tr><th>ID</th><th>Name</th><th>Edit date</th></tr>';
                    foreach($stmt as $row) {
                        echo '<tr>';
                        echo '<td>'. htmlspecialchars($row['user_id']) .'</td>';
                        echo '<td>'. htmlspecialchars($row['subscriber_name']) .'</td>';
                        echo '<td>'. htmlspecialchars($row['edit_date']) .'</td>';
                        echo '</tr>';
                    }
                    echo '</table>';

                    $sql = 'SELECT * FROM `deleted_users_history_view`';
                    $stmt = $pdo->query($sql);
                    echo '<h4>ad.4. Widok wykonanych akcji na już usuniętych użytkownikach</h4>';
                    echo '<table style="width:1000px">';
                    echo '<tr><th>ID</th><th>Name</th><th>Performed action</th><th>Action date</th></tr>';
                    foreach($stmt as $row) {
                        echo '<tr>';
                        echo '<td>'. htmlspecialchars($row['user_id']) .'</td>';
                        echo '<td>'. htmlspecialchars($row['subscriber_name']) .'</td>';
                        echo '<td>'. htmlspecialchars($row['action_performed']) .'</td>';
                        echo '<td>'. htmlspecialchars($row['action_date']) .'</td>';
                        echo '</tr>';
                    }
                    echo '</table>';

                    $sql = 'SELECT * FROM `existing_users_view`';
                    $stmt = $pdo->query($sql);
                    echo '<h4>ad.5. Widok wyświetlający tylko istniejących użytkowników (bez korzystania z tabelki subscribers)</h4>';
                    echo '<table style="width:1000px">';
                    echo '<tr><th>ID</th><th>Name</th></tr>';
                    foreach($stmt as $row) {
                        echo '<tr>';
                        echo '<td>'. htmlspecialchars($row['user_id']) .'</td>';
                        echo '<td>'. htmlspecialchars($row['subscriber_name']) .'</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
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