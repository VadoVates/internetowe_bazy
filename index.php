<!DOCTYPE html>
<html>
    <head>
        <title>Internetowe Bazy Danych</title>
    </head>
    <body>
        <h1>Wyzwalacz przed dodaniem użytkownika</h1>
        <h4>Dodaj użytkownika</h4>
        <form action="" method="POST">
        <p>Imię:</p>
        <input type="text" placeholder="First Name" id="subscriber_name" name="subscriber_name" required><br />
        <p>Email:</p>
        <input type="email" placeholder="Email" id="email" name="email" required><br /><br />
        <button type="submit">Register Subscriber</button>
        </form>
        <p>Po wciśnięciu "Dodaj", uruchomiony zostanie wyzwalacz przed dodaniem użytkownika do tabeli "subscribers"</p>

        <?php
            $host = 'localhost';
            $dbname = 'test';
            $username = 'int_baz';
            $password = '1nt3rn3t0w3_b4zy';
            $port = 3306;


            try {
                $pdo = new PDO('mysql:host=' . $host . ';dbname=' . $dbname . ';port=' . $port, $username, $password);
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $subscriber_name = $_POST['subscriber_name'];
                    $email = $_POST['email'];

                    $sql = 'INSERT INTO `subscribers` (`fname`, `email`) VALUES (:fname, :email)';
                    $stmt = $pdo->prepare($sql);

                    $stmt->bindParam(':fname', $subscriber_name, PDO::PARAM_STR);
                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);

                    if ($stmt->execute()) {
                        echo '<p>User added succesfully</p>';
                    } else {
                        echo '<p>User not added, there\'s babol somewhere</p>';
                    }
                }
            } catch (PDOException $e) {
                echo "Wystąpił błąd";
                $errormsg= "[" . date('Y-m-d H:i:s') . "] " . (string)$e . PHP_EOL;
                error_log($errormsg, 3, 'error_log.log');
            }
        ?>
    </body>
</html>