<?php
#Check for login and/or session
require_once 'security/check_auth.php';
#Load configuration file.
require_once 'c:\\inetpub\\wwwroot\\paystubs_resources\\config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Paystubs</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css'>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/all.css">
</head>

<body>
    <div class="background"></div>
    <!-- Header section with centered text and logout button -->
    <header class="header py-1">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-3">
                    <a href="default.php" class="btn btn-primary">Back</a>
                </div>
                <!-- Centered "Paystubs" text -->
                <div class="col-3">
                    <h1 class="mb-0">All Paystubs</h1>
                </div>
                <!-- Logout button aligned to the right -->
                <div class="col-3">
                    <a href="security/logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </header>
    <?php
    // Defining Variables
    require_once "variables.inc.php";

    // Get the average Rate (Averaging Raises)
    $stmt = $pdo->prepare("SELECT AVG(Rate) AS avg_rate FROM paystubs WHERE Rate > 0.00");
    $stmt->execute();
    // Fetch the result
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // Store the average rate in a variable
    $averageRate = $formatter->formatCurrency($row['avg_rate'], 'USD');

    // Initialize an array to hold totals for each numeric column
    $totals = [
        'Reg' => 0,
        'OT' => 0,
        'PTO' => 0,
        'HOL' => 0,
        'Gross' => 0,
        'Net' => 0,
        'Roth' => 0,
        'Bonus' => 0,
        'Bonus2' => 0,
        'Miles' => 0,
        'Leads' => 0,
        'Cell' => 0,
        '401KPC' => 0,
        'SS' => 0,
        'Med' => 0,
        'Fed' => 0,
        'State' => 0,
        'DPPO_F' => 0,
        'HSA_FE' => 0,
        'MD25F' => 0,
        'VIS_F' => 0
    ];

    // Get data from the table
    try {
        // Query to select all records from the table
        $sql = "SELECT 
            CO, 
            Pay_Date,  
            Rate,
            Reg, 
            OT, 
            PTO, 
            HOL,  
            Gross, 
            Net,
            Roth,
            Bonus, 
            Bonus2,
            Miles, 
            Leads, 
            Cell,
            401KPC, 
            SS,
            Med, 
            Fed,
            State,
            DPPO_F,
            HSA_FE,
            MD25F,
            VIS_F
            FROM paystubs
            ORDER BY Pay_Date DESC";

        $stmt = $pdo->prepare($sql); // Prepare the SQL query
        $stmt->execute(); // Execute the query

        // Fetch all results as an associative array
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Loop through each row of results and accumulate totals
        foreach ($results as $row) {
            foreach ($totals as $column => $total) {
                // Add the value from the current row to the total, assuming these columns are numeric
                if (isset($row[$column])) {
                    $totals[$column] += (float) str_replace("$", "", $row[$column]);
                }
            }
        }

        // START DISPLAY OF TABLE

        // If there are results, display them in an HTML table
        if (count($results) > 0) {
            $numRows = count($results); // Get the number of rows in the array
            // Start the HTML table with Bootstrap table classes and custom CSS
    ?>
            <div class='container-fluid d-flex justify-content-center content'>
                <div class='table-wrapper'>
                    <table class='table table-sm table-striped table-bordered table-hover'>
                        <?php
                        // Dynamically fetch and display column names as table headers
                        $columnCount = $stmt->columnCount(); // Get the number of columns
                        ?>
                        <thead class='table-dark'>
                            <tr> <!-- Start table header row -->
                                <?php
                                for ($i = 0; $i < $columnCount; $i++) {
                                    $columnMeta = $stmt->getColumnMeta($i); // Get column metadata
                                ?>
                                    <th scope="col"><?= htmlspecialchars($columnMeta['name']) ?></th> <!-- Print column name safely -->
                                <?php
                                }
                                ?>
                            </tr>
                        </thead> <!-- End header row -->
                        <!-- Loop through each row of results and display each cell in the table -->
                        <tbody>
                            <tr>
                                <td style="background-color: lightgreen"><strong></strong>LOC</td>
                                <td style="background-color: lightgreen"><strong></strong>TOTALS</td>
                                <td style="background-color: lightgreen"><strong><?= $averageRate ?></td>
                                <?php
                                $ColumnNum = 1;
                                foreach ($totals as $total) {
                                    if (in_array($ColumnNum, $ColumnsWithoutDollarSignsTotals)) {
                                ?>
                                        <td style="background-color: lightgreen"><strong><?= number_format($total, 2) ?></strong></td> <!-- Format the totals as currency -->
                                    <?php
                                    } elseif (in_array($ColumnNum, $ColumnswithNegativeTotals)) {
                                    ?>
                                        <td class="text-danger" style="background-color: lightgreen"><strong>-<?= $formatter->formatCurrency($total, 'USD') ?></strong></td> <!-- Format the totals as currency -->
                                    <?php
                                    } else {
                                    ?>
                                        <td style="background-color: lightgreen"><strong><?= $formatter->formatCurrency($total, 'USD') ?></strong></td> <!-- Format the totals as currency -->
                                <?php
                                    }
                                    $ColumnNum++;
                                }
                                ?>
                            </tr>
                            <?php
                            // Assuming $results is your array of rows, and column 2 is the paydate
                            foreach ($results as $row) {
                                $monitor_year = explode('-', $row["Pay_Date"])[0];
                                if ($previous_monitor_year != $monitor_year) {
                            ?>
                                    <tr> <!-- Start a new row -->
                                        <td colspan='<?= $columnCount ?>' class='fw-bold'><?= $monitor_year ?></td>
                                    </tr> <!--Start a new row -->
                                <?php
                                }
                                ?>
                                <tr> <!-- Start a new row -->
                                    <?php
                                    $ColumnNum = 1; // Initialize column counter
                                    foreach ($row as $cell) {
                                        // If we're on the second column (paydate)
                                        if ($ColumnNum == 2) {
                                            // Try to format the paydate using DateTime or another method
                                            $dateTime = new DateTime($cell);
                                            $formattedDate = $dateTime->format('m-d-Y'); // Format as month-day-year (US format)
                                    ?>
                                            <td><?= htmlspecialchars($formattedDate) ?></td>
                                        <?php
                                            // If this column is not supposed to have a dollar sign (based on your logic)
                                        } elseif (in_array($ColumnNum, $ColumnsWithoutDollarSignsCells)) {
                                        ?>
                                            <td><?= htmlspecialchars($cell) ?></td> <!-- Print each cell safely -->
                                        <?php
                                        } elseif (in_array($ColumnNum, $ColumnswithNegativeValues)) {
                                        ?>
                                            <td class="text-danger">-<?= $formatter->formatCurrency($cell, 'USD') ?></td> <!-- Print each cell safely -->
                                        <?php
                                            // For columns that require currency formatting
                                        } else {
                                        ?>
                                            <td><?= $formatter->formatCurrency($cell, 'USD') ?></td> <!-- Print currency formatted cell-->
                                    <?php
                                        }
                                        $ColumnNum++; // Move to the next column
                                    }
                                    ?>
                                </tr> <!-- Close the row -->
                            <?php
                                $previous_monitor_year = $monitor_year;
                            }
                            ?>
                        </tbody>

                        <!-- Table footer for totals -->
                        <tfoot>
                            <tr>
                                <td colspan="<?= $columnCount ?>">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="background-color: lightgreen"><strong></strong>LOC</td>
                                <td style="background-color: lightgreen"><strong></strong>TOTALS</td>
                                <td style="background-color: lightgreen"><strong><?= $averageRate ?></td>
                                <?php
                                $ColumnNum = 1;
                                foreach ($totals as $total) {
                                    if (in_array($ColumnNum, $ColumnsWithoutDollarSignsTotals)) {
                                ?>
                                        <td style="background-color: lightgreen"><strong><?= number_format($total, 2) ?></strong></td> <!-- Format the totals as currency -->
                                    <?php
                                    } elseif (in_array($ColumnNum, $ColumnswithNegativeTotals)) {
                                    ?>
                                        <td class="text-danger" style="background-color: lightgreen"><strong>-<?= $formatter->formatCurrency($total, 'USD') ?></strong></td> <!-- Format the totals as currency -->
                                    <?php
                                    } else {
                                    ?>
                                        <td style="background-color: lightgreen"><strong><?= $formatter->formatCurrency($total, 'USD') ?></strong></td> <!-- Format the totals as currency -->
                                <?php
                                    }
                                    $ColumnNum++;
                                }
                                ?>
                            </tr>
                        </tfoot>
                    </table> <!-- End the table -->
                <?php
            } else {
                ?>
                    <div class='alert alert-warning text-center' role='alert'>No records found. Add some data!</div> <!-- // Display message if no records are found -->
                <?php
            }
                ?>
                </div>
            </div>
        <?php
    } catch (PDOException $e) {
        // Handle any potential exceptions/errors
        ?>
            <div class='alert alert-danger' role='alert'>Error:<?= $e->getMessage() ?></div>
        <?php
    }

    $pdo = null; // Close the database connection
        ?>

        <!-- Include Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
        <script src="js/session_monitoring.js" defer></script>
        <script src="js/background.js"></script>
</body>

</html>