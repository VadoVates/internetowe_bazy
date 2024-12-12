<!DOCTYPE html>
<html>

<!-- Autor: Marek Górski -->
<!-- nr indeksu: 155647 -->
<!-- grupa D1 -->
<!-- rok akademicki 2024/2025 -->
<!-- semestr V -->
 
    <head>
        <title>Internetowe Bazy Danych</title>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
            ['Year', 'Sales', 'Expenses'],
            ['2004',  1000,      400],
            ['2005',  1170,      460],
            ['2006',  660,       1120],
            ['2007',  1030,      540]
            ]);

            var options = {
            title: 'Company Performance',
            curveType: 'function',
            legend: { position: 'bottom' }
            };

            var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

            chart.draw(data, options);
        }
        </script>

    </head>
    <body>
        <h1>Wykresy kursów walut</h1>

        <!--Div that will hold the pie chart-->
        <div id="curve_chart" style="width: 900px; height: 500px"></div>

        <?php
            $host = 'localhost';
            $dbname = 'test';
            $username = 'int_baz';
            $password = '1nt3rn3t0w3_b4zy';
            $port = 3306;


            try {
                $pdo = new PDO('mysql:host=' . $host . ';dbname=' . $dbname . ';port=' . $port, $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (PDOException $e) {
                echo "Wystąpił błąd";
                $errormsg= "[" . date('Y-m-d H:i:s') . "] " . (string)$e . PHP_EOL;
                error_log($errormsg, 3, 'error_log.log');
            }
        ?>
    </body>
</html>