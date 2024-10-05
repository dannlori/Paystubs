<?php
// Check if a session is not already started before calling session_start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'c:\\inetpub\\wwwroot\\paystubs_resources\\config.php';

// Check if the user is authenticated
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.php');
    exit;
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the submitted year
    $submittedYear = $_POST['selectedYear'] ?? '';
    $showData = 1;
} else {
    $submittedYear = "";
    $showData = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paystubs</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">        
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            background: linear-gradient(to right, #ff7e5f, #feb47b); /* Adjust colors */
        }

        .fullscreen-div {
            width: 100vw;  /* Full width of the viewport */
            height: 100vh; /* Full height of the viewport */
            background-color: lightblue; /* Just to see the div visually */
        }
        
        /* No Files */
        .custom-no-files {
            text-align: center;
            color: red;
        }

        #drop-area {
            border: 2px dashed #ccc;
            width: 300px;
            padding: 30px;
            text-align: center;
            margin: 0 auto;
        }

        #drop-area.highlight {
            border-color: green;
        }

        #fileElem {
            display: none;
        }

        /* Scrollable container */
        .table-wrapper {
            max-height: 700px; /* Adjust height as needed */
            overflow-y: auto;  /* Enable vertical scrolling */
            width: max-content;
        }

        thead th {
            position: sticky;
            top: 0; /* Fix the header at the top */
            /* background-color: #f8f9fa; /* Add background to prevent overlap */
            background-color: coral;
            z-index: 1; /* Ensure it stays on top */
        }

        .message {
            transition: opacity 1s ease; /* 1 second fade out effect */
            opacity: 1; /* Fully visible */
        }

        .message.fade-out {
            opacity: 0; /* Fully transparent */
        }

        /* Floating div in the top right corner */
        .floating-upload {
            position: fixed;
            top: 30px;
            right: 10px;
            background-color: white;
            border: 1px solid #ccc;
            padding: 10px;
            width: 350px;
            height: auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            cursor: move;
            z-index: 1000;
            resize: none; /* Prevent resizing */
            overflow: hidden; /* Prevent scrollbars */
            opacity: 0.85; /* Semi-transparent */
        }
    </style>

    <form id="yearForm" method="POST" action="">
        <input type="hidden" name="selectedYear" id="hiddenYearInput" value="">
    </form>
</head>
<body>
    
<?php
    // Defining Variables
    require_once "variables.inc.php";

    // Get the total number of PDF files
    $pdfFiles = glob("$directory\\*.pdf");
    $totalFiles = count($pdfFiles);

    // If no files are found, display a message and exit the script
    if ($totalFiles == 0) {
        $noFiles = 1;
        //exit;
    }

    // Get the average Rate (Averaging Raises)
    $stmt = $pdo->prepare("SELECT AVG(Rate) AS avg_rate FROM paystubs WHERE Rate > 0.00");
    $stmt->execute();
    // Fetch the result
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // Store the average rate in a variable
    $averageRate = $formatter->formatCurrency($row['avg_rate'], 'USD');

    // Initialize an array to keep track of processed files
    $processedFiles = array();

    // Define the SQL statement for inserting data into the database
    $sql = "INSERT INTO paystubs (
            co, 
            pay_date, 
            rate, 
            reg, 
            ot, 
            pto, 
            hol,  
            bonus, 
            bonus2, 
            miles, 
            leads, 
            cell, 
            gross, 
            401kpc, 
            ss, 
            med, 
            fed, 
            state, 
            net,
            roth,
            DPPO_F,
            HSA_FE,
            MD25F,
            VIS_F
            ) 
            VALUES (
            :co, 
            :pay_date, 
            :rate, 
            :reg, 
            :ot, 
            :pto, 
            :hol,  
            :bonus, 
            :bonus2, 
            :miles, 
            :leads, 
            :cell, 
            :gross, 
            :401kpc, 
            :ss, 
            :med, 
            :fed, 
            :state, 
            :net,
            :roth,
            :DPPO_F,
            :HSA_FE,
            :MD25F,
            :VIS_F
            )";

    // Prepare the insert statement
    $stmt = $pdo->prepare($sql);

    // Loop through PDF files in the directory
    foreach ($pdfFiles as $index => $pdfPath) {

        // Check if the file has already been processed
        if (in_array($pdfPath, $processedFiles)) {
            // Move to not processed folder
            $justfilename = basename($pdfPath);
            $destination = $notProcessedDirectory . $justfilename;
            rename($pdfPath, $destination);
            ?>
            <div class="message alert alert-warning">
                <?=$justfilename?> has already been processed. Moved to not processed folder.<br/>
            </div>
            <?php
            continue;
        }

        // Mark the file as processed
        $processedFiles[] = $pdfPath;

        // Extract text from PDF
        $command = $popplerExe . "-layout " . $pdfPath . " -";
        $text = shell_exec($command);

        if ($text === null) {
        ?>
            Error running pdftotext command: <?=$pdfPath?> <br/>
            Command: <?=$command?><br/><br/>  <!-- Output the actual command for debugging -->
        <?php    
            // Move to not processed folder
            $justfilename = basename($pdfPath);
            $destination = $notProcessedDirectory . $justfilename;
            rename($pdfPath, $destination);
        ?>
            <div class="message alert alert-danger">
            Failed to extract text from <?=$justfilename?>. Moved to not processed folder.<br/>
            </div>
        <?php
            continue;
        }

        // Extract information using regular expressions
        $pdfInfo = array(
            'co' => "LOC",
            'pay_date' => pathinfo($pdfPath, PATHINFO_FILENAME),
            'rate' => extractValue($text, '/HRLY\s+\$([\d,]+\.\d{2})(?=\s+[\d,]*\.\d{2}|$)/'),
            'reg' => extractValue($text, '/HRLY\s+\$[\d,]+\.\d{2}\s+([\d.]+)\s+\$/'),
            'ot' => extractValue($text, '/OT\s+\$[\d,]+\.\d{2}\s+([\d.]+)\s+\$/'),
            'pto' => extractValue($text, '/PTO\s+\$[\d,]+\.\d{2}\s+([\d.]+)\s+\$/') + extractValue($text, '/SICK\s+\$[\d,]+\.\d{2}\s+([\d.]+)\s+\$/'),
            'hol' => extractValue($text, '/HOL\s+\$[\d,]+\.\d{2}\s+([\d.]+)\s+\$/'),
            'gross' => extractValue($text, '/Totals(?:\s+[\d.]+)?\s+\$([\d,]+\.\d{2})/'),
            'net' => extractValue($text, '/Net Wages\s+\$([\d,]+\.\d{2})/'),
            'roth' => extractValue($text, '/ROTH%\s+\$([\d,]+\.\d{2})(?=\s+\$|\s*$)/'),
            'bonus' => extractValue($text, '/BONUS\s+\$([\d,]+\.\d{2})(?=\s+\$|\s*$)/'),
            'bonus2' => extractValue($text, '/BONUS2\s+\$([\d,]+\.\d{2})(?=\s+\$|\s*$)/'),
            'miles' => extractValue($text, '/MILES\s+\$([\d,]+\.\d{2})(?=\s+\$|\s*$)/'),
            'leads' => extractValue($text, '/LEADS\s+\$([\d,]+\.\d{2})(?=\s+\$|\s*$)/'),
            'cell' => extractValue($text, '/CELL\s+\$([\d,]+\.\d{2})(?=\s+\$|\s*$)/'),
            '401kpc' => extractValue($text, '/401KPC\s+\$([\d,]+\.\d{2})(?=\s+\$|\s*$)/'),
            'ss' => extractValue($text, '/SS\s+\$([\d,]+\.\d{2})/'),
            'med' => extractValue($text, '/MED\s+\$([\d,]+\.\d{2})/'),
            'fed' => extractValue($text, '/Federal\s+\$([\d,]+\.\d{2})\s+\$[\d,]+\.\d{2}/'),
            'state' => extractValue($text, '/State\s+\$([\d,]+\.\d{2})\s+\$[\d,]+\.\d{2}/'),
            'DPPO_F' => extractValue($text, '/DPPO-F\s+\$([\d,]+\.\d{2})\s+\$[\d,]+\.\d{2}/'),
            'HSA_FE' => extractValue($text, '/HSA-FE\s+\$([\d,]+\.\d{2})\s+\$[\d,]+\.\d{2}/'),
            'MD25F' => extractValue($text, '/MD25F\s+\$([\d,]+\.\d{2})\s+\$[\d,]+\.\d{2}/'),
            'VIS_F' => extractValue($text, '/VIS-F\s+\$([\d,]+\.\d{2})\s+\$[\d,]+\.\d{2}/')
        );

        // Check if the pay_date already exists in the database
        $checkSql = "SELECT COUNT(*) FROM paystubs WHERE pay_date = :pay_date";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute(['pay_date' => $pdfInfo['pay_date']]);

        // Fetch the count of records with the same pay_date
        $existingCount = $checkStmt->fetchColumn();
        
        // If the pay_date exists, skip the insertion and move the file
    if ($existingCount > 0) {
        // Move to not processed folder
        $justfilename = basename($pdfPath);
        $destination = $notProcessedDirectory . $justfilename;
        rename($pdfPath, $destination);
        ?>
        <div class="message alert alert-warning">
            The pay date <?=$pdfInfo['pay_date']?> already exists. Moved <?=$justfilename?> to not processed folder.<br/>
        </div>
        <?php
        continue; // Skip to the next file
    }

        // Execute the prepared statement to insert data into the database
        $stmt->execute($pdfInfo);

        // Flush the output buffer to immediately display the progress update
        flush();
        $justfilename = basename($pdfPath);
        $source = $pdfPath;
        $destination = $processedDirectory . $justfilename;

        if (rename($source, $destination)) {
?>
            <div class="message alert alert-success">
                <?=$justfilename?> moved from <?=dirname($source)?>  to <?=$processedDirectory?> successfully!<br/>
            </div>

<?php
        } else {
?>
            <div class="message alert alert-danger">
                Failed to move the <?=$justfilename?> from <?=dirname($source)?> to <?=$processedDirectory?><br/>
            </div>
<?php
        }
    }

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
    // Query to select all records from the table
    $sql = "SELECT DISTINCT YEAR(Pay_Date) AS year 
        FROM paystubs
        ORDER BY year";
        
    $stmt = $pdo->prepare($sql); // Prepare the SQL query
    $stmt->execute(); // Execute the query

    // Fetch all results as an associative array
    $uniqueYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    ?>
    <!-- Header section with centered text and logout button -->
    <header class="bg-light py-2">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-3">
                    <select id="yearDropdown" onchange="selectYear()">
                        <option value="NOTHING">Select Year</option>
                        <option value="all">ALL YEARS</option>
                    </select>
                </div> 
                <div class="col-3">
                    <h1 class="mb-0"><?=$submittedYear?> Paystubs</h1>
                </div>
                <!-- Logout button aligned to the right -->
                <div class="col-3">
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    <?php

    //ONLY SHOW IF DATE IS SELECTED
    if ($showData == 1) {
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
                Where YEAR(Pay_Date) = $submittedYear
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
                        $totals[$column] += (float) str_replace("$","",$row[$column]);
                    }
                }
            }

            // START DISPLAY OF TABLE

            // If there are results, display them in an HTML table
            if (count($results) > 0) {
                $numRows = count($results); // Get the number of rows in the array
                // Start the HTML table with Bootstrap table classes and custom CSS
                ?>
                <div class='container-fluid d-flex justify-content-center'>
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
                                        <th scope="col"><?=htmlspecialchars($columnMeta['name'])?></th> <!-- Print column name safely -->
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
                                    <td style="background-color: lightgreen"><strong><?=$averageRate?></td>
                                    <?php
                                    $ColumnNum = 1;
                                    foreach ($totals as $total) {
                                        if (in_array($ColumnNum, $ColumnsWithoutDollarSignsTotals)) {
                                    ?>
                                            <td style="background-color: lightgreen"><strong><?=number_format($total, 2)?></strong></td> <!-- Format the totals as currency -->
                                        <?php
                                        } elseif (in_array($ColumnNum, $ColumnswithNegativeTotals)) {
                                        ?>
                                            <td class="text-danger" style="background-color: lightgreen"><strong>-<?=$formatter->formatCurrency($total, 'USD')?></strong></td> <!-- Format the totals as currency -->
                                        <?php
                                        } else {
                                        ?>
                                            <td style="background-color: lightgreen"><strong><?=$formatter->formatCurrency($total, 'USD')?></strong></td> <!-- Format the totals as currency -->
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
                                            <td colspan='<?=$columnCount?>' class='fw-bold'><?=$monitor_year?></td>
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
                                                <td><?=htmlspecialchars($formattedDate)?></td>
                                            <?php
                                            // If this column is not supposed to have a dollar sign (based on your logic)
                                            } elseif (in_array($ColumnNum, $ColumnsWithoutDollarSignsCells)) {
                                            ?>
                                                <td><?=htmlspecialchars($cell)?></td> <!-- Print each cell safely -->
                                            <?php
                                            } elseif (in_array($ColumnNum, $ColumnswithNegativeValues)) {
                                            ?>
                                                <td class="text-danger">-<?=$formatter->formatCurrency($cell, 'USD')?></td> <!-- Print each cell safely -->
                                            <?php
                                            // For columns that require currency formatting
                                            } else {
                                            ?>
                                                <td><?=$formatter->formatCurrency($cell, 'USD')?></td> <!-- Print currency formatted cell-->
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
                                    <td colspan="<?=$columnCount?>">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="background-color: lightgreen"><strong></strong>LOC</td>
                                    <td style="background-color: lightgreen"><strong></strong>TOTALS</td>
                                    <td style="background-color: lightgreen"><strong><?=$averageRate?></td>
                                    <?php
                                    $ColumnNum = 1;
                                    foreach ($totals as $total) {
                                        if (in_array($ColumnNum, $ColumnsWithoutDollarSignsTotals)) {
                                    ?>
                                            <td style="background-color: lightgreen"><strong><?=number_format($total, 2)?></strong></td> <!-- Format the totals as currency -->
                                        <?php
                                        } elseif (in_array($ColumnNum, $ColumnswithNegativeTotals)) {
                                        ?>
                                            <td class="text-danger" style="background-color: lightgreen"><strong>-<?=$formatter->formatCurrency($total, 'USD')?></strong></td> <!-- Format the totals as currency -->
                                        <?php
                                        } else {
                                        ?>
                                            <td style="background-color: lightgreen"><strong><?=$formatter->formatCurrency($total, 'USD')?></strong></td> <!-- Format the totals as currency -->
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
                <div class='alert alert-danger' role='alert'>Error:<?=$e->getMessage()?></div>
            <?php
            }
    } else {
    ?>
        <div class="container container-fluid">
            <div class="row">
                <div class="col fw-bold py-3">
                    SELECT ^ TO VIEW
                </div>
            </div>

        </div>
    <?php
    }

    $pdo = null; // Close the database connection
    /*
    if ($noFiles == 1) {
    ?>
        <p class='h1 custom-no-files'>No files found in the <?=$directory?> to process.</p><br/>
    <?php
    }
    */
        // Function to extract a value using a regular expression
        function extractValue($text, $pattern) {
            if (preg_match($pattern, $text, $match)) {
                // Remove commas from the matched value
                $number = str_replace(',', '', $match[1]);
                return floatval($number);
            } else {
                return 0.0; // Set default value if not found
            }
        }
        ?>
        <div id="uploadForm" class="floating-upload">
            <?php
            // Include the file upload .inc file at the bottom 
            include 'submitFiles.inc'; 
            ?>
        </div>

        <!-- Include Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Fetch total files from PHP (make sure this is properly defined in PHP)
                // let totalFiles = <?php echo $totalFiles; ?>;

                // Check if totalFiles is defined and greater than 0
                if (typeof totalFiles !== 'undefined' && totalFiles > 0) {
                    let index = 0;

                    // Initialize toast with autohide set to false
                    let progressToast = new bootstrap.Toast(document.getElementById('progressToast'), {
                        autohide: false
                    });
                    progressToast.show();

                    // Function to update progress
                    function updateProgress() {
                        if (index < totalFiles) {
                            index++;
                            let progress = Math.floor((index / totalFiles) * 100);
                            let progressBar = document.getElementById('progressBar');
                            progressBar.style.width = progress + '%';
                            progressBar.setAttribute('aria-valuenow', progress);
                            progressBar.innerHTML = progress + '%';
                        } else {
                            clearInterval(progressInterval);  // Stop updating once completed
                        }
                    }

                    // Update progress every 100ms (simulate processing delay)
                    let progressInterval = setInterval(updateProgress, 100);
                }
            });   

            <!-- JavaScript to fade out all messages one by one -->

            window.onload = function() {
                var messages = document.getElementsByClassName('message');
                var delay = 0; // Initial delay

                for (var i = 0; i < messages.length; i++) {
                    (function(index) {
                        // Fade out the message after a delay
                        setTimeout(function() {
                            messages[index].classList.add('fade-out'); // Add the fade-out class
                            
                            // Set display to none after fade-out is complete
                            setTimeout(function() {
                                messages[index].style.display = 'none'; // Remove the message from display
                            }, 1000); // Match this duration to the CSS transition duration (1 second)
                        }, delay);
                        delay += 2000; // Increment delay by 3 seconds for each message
                    })(i);
                }
            };

            // Make the upload form draggable
            const uploadForm = document.getElementById("uploadForm");

            let isMouseDown = false;
            let offsetX = 0;
            let offsetY = 0;

            uploadForm.addEventListener("mousedown", function(e) {
                isMouseDown = true;
                offsetX = e.clientX - uploadForm.getBoundingClientRect().left;
                offsetY = e.clientY - uploadForm.getBoundingClientRect().top;
                document.addEventListener("mousemove", moveElement);
            });

            document.addEventListener("mouseup", function() {
                isMouseDown = false;
                document.removeEventListener("mousemove", moveElement);
            });

            function moveElement(e) {
                if (isMouseDown) {
                    uploadForm.style.left = e.clientX - offsetX + "px";
                    uploadForm.style.top = e.clientY - offsetY + "px";
                    uploadForm.style.position = "absolute";
                }
            }

            // PHP variable containing unique years
            const uniqueYears = <?php echo json_encode($uniqueYears); ?>;

            // Populate the dropdown
            const dropdown = document.getElementById('yearDropdown');
            uniqueYears.forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                dropdown.appendChild(option);
            });

            function selectYear() {
                const selectedYear = document.getElementById("yearDropdown").value;
                if (selectedYear === 'all') {
                    // Redirect to all.php if "All" is selected
                    window.location.href = 'all.php';
                } else if (selectedYear) {
                    // Set the hidden input's value
                    document.getElementById('hiddenYearInput').value = selectedYear;

                    // Submit the form
                    document.getElementById('yearForm').submit();
                }
            }
        </script>
    </body>
</html>