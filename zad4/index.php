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
    <title>Wykresy kursów walut</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <?php if (!empty($filteredData)): ?>
    <script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawCharts);

    function drawCharts() {
        var dataEUR = new google.visualization.DataTable();
        var dataUSD = new google.visualization.DataTable();

        // Dodanie kolumn
        dataEUR.addColumn('datetime', '<?php echo $headers[0]; ?>');
        dataEUR.addColumn('number', '<?php echo $headers[1]; ?>');
        dataEUR.addColumn('number', '<?php echo $headers[2]; ?>');

        dataUSD.addColumn('datetime', '<?php echo $headers[0]; ?>');
        dataUSD.addColumn('number', '<?php echo $headers[3]; ?>');
        dataUSD.addColumn('number', '<?php echo $headers[4]; ?>');

        // Dodanie wierszy danych
        dataEUR.addRows([
            <?php
            foreach ($filteredData as $row) {
                echo "[new Date('" . $row[0] . "'), " . (float)$row[1] . ", " . (float)$row[2] . "],\n";
            }
            ?>
        ]);

        dataUSD.addRows([
            <?php
            foreach ($filteredData as $row) {
                echo "[new Date('" . $row[0] . "'), " . (float)$row[3] . ", " . (float)$row[4] . "],\n";
            }
            ?>
        ]);

        // Opcje wykresów
        var optionsEUR = {
            title: 'Kursy EUR',
            legend: { position: 'bottom' },
            hAxis: { title: '<?php echo $headers[0]; ?>' },
            vAxis: { title: 'Wartość' }
        };

        var optionsUSD = {
            title: 'Kursy USD',
            legend: { position: 'bottom' },
            hAxis: { title: '<?php echo $headers[0]; ?>' },
            vAxis: { title: 'Wartość' }
        };

        // Rysowanie wykresów
        var chartEUR = new google.visualization.LineChart(document.getElementById('chart_eur'));
        chartEUR.draw(dataEUR, optionsEUR);

        var chartUSD = new google.visualization.LineChart(document.getElementById('chart_usd'));
        chartUSD.draw(dataUSD, optionsUSD);
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

    <!-- Div na wykresy -->
    <?php if (!empty($filteredData)): ?>
        <div id="chart_eur" style="width: 100%; height: 500px"></div>
        <div id="chart_usd" style="width: 100%; height: 500px"></div>
    <?php endif; ?>
</body>
</html>
