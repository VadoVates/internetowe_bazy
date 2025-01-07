<?php
session_start();

$headers = [];
$data = [];
$filteredData = [];

// Resetowanie sesji
if (isset($_POST['reset_session'])) {
    session_unset();
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Krok 1: Przesłanie pliku CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $filePath = $_FILES['csv_file']['tmp_name'];
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ",");
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $data[] = $row;
        }
        fclose($handle);

        // Zapis danych w sesji
        $_SESSION['headers'] = $headers;
        $_SESSION['csv_data'] = $data;

        // Wyznaczenie min i max daty
        $dates = array_column($data, 0);
        $_SESSION['min_date'] = min($dates);
        $_SESSION['max_date'] = max($dates);

        // Przekierowanie po przesłaniu pliku
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Krok 2: Filtrowanie danych na podstawie dat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_date'], $_POST['end_date'])) {
    $data = $_SESSION['csv_data'] ?? [];
    $headers = $_SESSION['headers'] ?? [];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    // Walidacja i zamiana dat, jeśli start_date > end_date
    if ($startDate > $endDate) {
        $temp = $startDate;
        $startDate = $endDate;
        $endDate = $temp;
    }

    foreach ($data as $row) {
        $rowDate = date('Y-m-d', strtotime($row[0]));
        if ($rowDate >= $startDate && $rowDate <= $endDate) {
            $filteredData[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html> 
<head>
    <title>Internetowe Bazy Danych</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <?php if (!empty($filteredData)): ?>
    <script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = new google.visualization.DataTable();

        // Dodanie kolumn
        <?php
        echo "data.addColumn('datetime', '" . $headers[0] . "');\n"; // Pierwsza kolumna to datetime
        for ($i = 1; $i < count($headers); $i++) {
            // Ignorujemy kolumny 'date' i 'time'
            if ($headers[$i] !== 'date' && $headers[$i] !== 'time') {
                echo "data.addColumn('number', '" . $headers[$i] . "');\n";
            }
        }
        ?>

        // Dodanie wierszy danych
        data.addRows([
            <?php
            foreach ($filteredData as $row) {
                echo "[new Date('" . $row[0] . "'), ";
                for ($i = 1; $i < count($row); $i++) {
                    // Ignorujemy kolumny 'date' i 'time'
                    if ($headers[$i] !== 'date' && $headers[$i] !== 'time') {
                        echo (float)$row[$i];
                        if ($i < count($row) - 1) echo ", ";
                    }
                }
                echo "],\n";
            }
            ?>
        ]);

        var options = {
            title: 'Historia kursów wymiany',
            legend: { position: 'bottom' },
            hAxis: { title: '<?php echo $headers[0]; ?>' },
            vAxis: { title: 'wartość' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
        chart.draw(data, options);
    }
    </script>
    <?php endif; ?>
</head>
<body>
    <h1>Wykresy kursów walut</h1>

    <?php if (!isset($_SESSION['csv_data']) || empty($_SESSION['csv_data'])): ?>
        <!-- Formularz do przesyłania pliku CSV -->
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" required>
            <button type="submit">Prześlij plik CSV</button>
        </form>
    <?php elseif (!isset($_POST['start_date'], $_POST['end_date'])): ?>
        <!-- Formularz do wyboru zakresu dat -->
        <form method="POST">
            <label for="start_date">Data początkowa:</label>
            <input type="date" name="start_date" value="<?php echo isset($_SESSION['min_date']) ? date('Y-m-d', strtotime($_SESSION['min_date'])) : ''; ?>" min="<?php echo isset($_SESSION['min_date']) ? date('Y-m-d', strtotime($_SESSION['min_date'])) : ''; ?>" max="<?php echo isset($_SESSION['max_date']) ? date('Y-m-d', strtotime($_SESSION['max_date'])) : ''; ?>" required>

            <label for="end_date">Data końcowa:</label>
            <input type="date" name="end_date" value="<?php echo isset($_SESSION['max_date']) ? date('Y-m-d', strtotime($_SESSION['max_date'])) : ''; ?>" min="<?php echo isset($_SESSION['min_date']) ? date('Y-m-d', strtotime($_SESSION['min_date'])) : ''; ?>" max="<?php echo isset($_SESSION['max_date']) ? date('Y-m-d', strtotime($_SESSION['max_date'])) : ''; ?>" required>

            <button type="submit">Wygeneruj wykres</button>
        </form>
    <?php endif; ?>

    <!-- Przycisk resetowania sesji -->
    <form method="POST">
        <button type="submit" name="reset_session">Resetuj sesję</button>
    </form>

    <!-- Div na wykres -->
    <?php if (!empty($filteredData)): ?>
        <div id="curve_chart" style="width: 100%; height: 500px"></div>
    <?php endif; ?>
</body>
</html>
