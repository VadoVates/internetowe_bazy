<?php

// Autor: Marek Górski
// nr indeksu: 155647
// grupa D1
// rok akademicki 2024/2025
// semestr V

    $host = 'localhost';
    $dbname = 'test';
    $username = 'int_baz';
    $password = '1nt3rn3t0w3_b4zy';
    $port = 3306;


    try {
        $pdo = new PDO('mysql:host=' . $host . ';dbname=' . $dbname . ';port=' . $port, $username, $password);

        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $id = $_GET['id'];

            $sql = 'DELETE FROM `subscribers` WHERE `id` = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                header('Location: viewsubscribers.php');
                exit;
            } else {
                echo '<p>Wystąpił babol, nie udało się skasować usera</p>';
            }
        } else {
            echo '<p>Nieprawidłowe ID</p>';
        }
    } catch (PDOException $e) {
        echo "Wystąpił błąd";
        $errormsg= "[" . date('Y-m-d H:i:s') . "] " . (string)$e . PHP_EOL;
        error_log($errormsg, 3, 'error_log.log');
    }

?>