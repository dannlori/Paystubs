<?php
require_once "functions.php";

// Get the total number of PDF files
$pdfFiles = glob("$directory\\*.pdf");
$totalFiles = count($pdfFiles);

// If no files are found, display a message and exit the script
if ($totalFiles == 0) {
    $noFiles = 1;
    //exit;
}

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
        'bonus' => sumValues(extractAllValues($text, '/BONUS\s+\$([\d,]+\.\d{2})(?=\s+\$|\s*$)/')),
        'bonus2' => sumValues(extractAllValues($text, '/BONUS2\s+\$([\d,]+\.\d{2})(?=\s+\$|\s*$)/')),
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


/*
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
*/
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