<?php
#Check for login and/or session
require_once 'security/check_auth.php';

#Load configuration file.
require_once 'c:\\inetpub\\wwwroot\\paystubs_resources\\config.php';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the submitted year
    $submittedYearText = $_POST['selectedYear'] . ' Paystubs' ?? '';
    $submittedYear = $_POST['selectedYear'] ?? '';
    $showData = 1;
} else {
    $submittedYearText = "<span id='make_selection'><-- Make a selection</span>";
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
        <!-- Font Awesome CSS -->
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css'>      
        <style>
            body, html {
                margin: 0;
                padding: 0;
                height: 100%;
                overflow: hidden; /* Prevent scrolling */
            }

            .background {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-size: cover; /* Cover the entire area */
                background-position: center; /* Center the image */
                z-index: -1; /* Make sure it stays in the background */
            }

            .content {
                position: relative;
                z-index: 1; /* Ensure content is above the background */
                color: white; /* Change text color for contrast */
                text-align: center;
                padding: 20px;
                opacity: 95%;
            }

            .fullscreen-div {
                width: 100vw;  /* Full width of the viewport */
                height: 100vh; /* Full height of the viewport */
                background-color: lightblue; /* Just to see the div visually */
            }
            
            #make_selection {
                text-align:center;
                vertical-align: baseline ;
                font-size: 30px;
            }

            /* No Files */
            .custom-no-files {
                text-align: center;
                color: red;
            }

            #drop-area {
                border: 2px dashed #ccc;
                width: 200px;
                padding: 10px;
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
                top: 85%;
                right: 20px;
                background-color: lightblue;
                border: 1px solid #ccc;
                padding: 5px;
                width: 210px;
                height: auto;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                cursor: move;
                z-index: 1000;
                resize: none; /* Prevent resizing */
                overflow: hidden; /* Prevent scrollbars */
                opacity: 0.85; /* Semi-transparent */
            }

            /* Close button */
            .close-btn {
                position: absolute;
                top: 5px;
                right: 10px;
                cursor: pointer;
                font-size: 18px;
                color: black;
            }

            .close-btn:hover {
                color: red;
            }

            #sessionAlert {
                display: none; /* Initially hidden */
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: white;
                border: 1px solid black;
                padding: 20px;
                z-index: 1000;
                opacity: 1;
                transition: opacity 1s; /* Transition for fading */
                border-radius: 10px; /* Rounded corners */
            }

            #closeAlert {
                cursor: pointer;
                float: right;
                font-size: 20px;
                line-height: 20px; /* Center the close button vertically */
            }

        </style>

        <form id="yearForm" method="POST" action="">
            <input type="hidden" name="selectedYear" id="hiddenYearInput" value="">
        </form>

        <?php
        // Defining Variables
        require_once "variables.inc.php";

        // Upload Code for adding files into DB
        include "uploadCode.inc.php";
        
        // Get the average Rate (Averaging Raises)
        $stmt = $pdo->prepare("SELECT AVG(Rate) AS avg_rate FROM paystubs WHERE Rate > 0.00");
        $stmt->execute();
        // Fetch the result
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // Store the average rate in a variable
        $averageRate = $formatter->formatCurrency($row['avg_rate'], 'USD');
        ?>
    </head>
    <body>
        <div class="background"></div>
        <?php
        // Get years to populate "Select Year" dropdown from the table
        $sql = "SELECT DISTINCT YEAR(Pay_Date) AS year FROM paystubs ORDER BY year";
        $stmt = $pdo->prepare($sql); // Prepare the SQL query
        $stmt->execute(); // Execute the query

        // Fetch all results as an associative array to detect if there is anything in the database
        $uniqueYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if ($uniqueYears) {
            $dataInDB = "";
            $selectDataText = "SELECT ^ TO VIEW";
        } else {
            $dataInDB = "<p><b><center>No Files in Database. Add some files.</center></b></p>";
            $selectDataText = "Upload Files to start! -->";
        }
        ?>

        <div id="sessionAlert" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background-color:white; border:1px solid black; padding:20px; z-index:1000; opacity:1; transition: opacity 1s; border-radius:10px;">
            <span id="closeAlert" style="cursor:pointer; float:right;">&times;</span>
            Your session will expire in <span id="countdown">30</span> seconds.
        </div>

        <!-- Header section with dropdown, centered text and logout button -->
        <header class="bg-light py-1">
            <div class="container">
                <div class="row align-items-center justify-content-between">
                    <div class="col-3">
                        <select id="yearDropdown" onchange="selectYear()">
                            <option value="">Select Year</option>
                            <option value="all">ALL YEARS</option>
                        </select>
                    </div> 
                    <div class="col-3">
                        <h1 class="mb-0"><?=$submittedYearText?></h1>
                    </div>
                    <!-- Logout button aligned to the right -->
                    <div class="col-3">
                        <a href="security/logout.php" class="btn btn-danger">Logout</a>
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
                    <div class="col-3 fw-bold py-3">
                        <?=$selectDataText?>
                    </div>
                    <div class="col-3 fw-bold py-3">
                        <?=$dataInDB?>
                    </div>
                </div>

            </div>
        <?php
        }

        $pdo = null; // Close the database connection

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
            <span class="close-btn" onclick="hideuploadForm()" title="Hide window">×</span>
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

            function hideuploadForm() {
                document.getElementById('uploadForm').style.display = 'none';
            }
            
            const backgrounds = [
                'url("images/paystubs1.png")',
                'url("images/paystubs2.jpg")',
                'url("images/paystubs3.jpg")',
                'url("images/paystubs4.jpg")'
            ];

            // Select a random background image
            const randomIndex = Math.floor(Math.random() * backgrounds.length);
            const backgroundDiv = document.querySelector('.background');
            backgroundDiv.style.backgroundImage = backgrounds[randomIndex];

        </script>
        <script src="security/js/session_monitoring.js" defer></script>
    </body>
</html>