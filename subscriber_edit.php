<!DOCTYPE html>
<html>
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
        <h1>Edycja użytkowników</h1>
            <?php
            $host = 'localhost';
            $dbname = 'test';
            $username = 'int_baz';
            $password = '1nt3rn3t0w3_b4zy';
            $port = 3306;

            try {
                if (isset($_GET['id']) && is_numeric($_GET['id'])) {

                    $id = $_GET['id'];

                    $pdo = new PDO('mysql:host=' . $host . ';dbname=' . $dbname . ';port=' . $port, $username, $password);
                    $sql = 'SELECT * FROM `subscribers` WHERE `id` = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();

                    $subscriber = $stmt->fetch(PDO::FETCH_ASSOC);
                    if(!$subscriber) {
                        echo '<p>Nie znaleziono użytkownika z tym ID.</p>';
                        exit;
                    }

                    echo '<form action="" method="POST">';
                    echo '<p>Imię:</p>';
                    echo '<input type="text" value="' . htmlspecialchars($subscriber['fname']) . '" id="subscriber_name" name="subscriber_name" required><br />';
                    echo '<p>Email:</p>';
                    echo '<input type="email" value="' . htmlspecialchars($subscriber['email']) . '" id="email" name="email" required><br /><br />';
                    echo '<button type="submit">Edit Subscriber</button>';
                    echo '</form>';

                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $fname = $_POST['subscriber_name'];
                        $email = $_POST['email'];

                        $sql = 'UPDATE `subscribers` SET `fname` = :fname, `email` = :email WHERE `id` = :id';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':fname', $fname, PDO::PARAM_STR);
                        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                        $stmt->bindParam(':id', $id, PDO::PARAM_STR);

                        if ($stmt->execute()) {
                            header('Location: viewsubscribers.php');
                            exit;
                        } else {
                            echo '<p>Wystąpił błąd podczas aktualizacji</p>';
                        }
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
                
    </body>
</html>