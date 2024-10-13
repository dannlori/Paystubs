<?php


$directory = 'c:\\paystubs'; // Directory containing PDF files
$processedDirectory = $directory . '\\processed\\'; // Append the 'processed' directory
// Define the path for the not processed directory
$notProcessedDirectory = $directory . '\\notprocessed\\'; // Change this to your actual path

$popplerExe = "C:\\poppler\\pdftotext.exe ";
$noFiles = 0;
$ColumnsWithoutDollarSignsCells = [1, 2, 4, 5, 6, 7];
$ColumnsWithoutDollarSignsTotals = [1, 2, 3, 4];
$ColumnswithNegativeValues = [17, 18, 19, 20, 21, 22, 23, 24];
$ColumnswithNegativeTotals = [14, 15, 16, 17, 18, 19, 20, 21];
$previous_monitor_year = "";

// Path to the file containing the database credentials
$credentialsFile = 'C:\\inetpub\\wwwroot\\paystubs_resources\\db.info';  // Change this to the actual path

// Read and parse the credentials file
if (file_exists($credentialsFile)) {
    $dbCredentials = parse_ini_file($credentialsFile);

    // Assign credentials to variables
    $dbUsername = $dbCredentials['username'];
    $dbPassword = $dbCredentials['password'];

} else {
    die('Error: Credentials file not found.');
}

// Create a NumberFormatter instance for currency
$locale = 'en_US';  // You can specify different locales (e.g., 'de_DE' for Germany, 'fr_FR' for France)
$formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

// PDO database connection
$dsn = 'mysql:host=localhost;dbname=earnings';
$username = $dbUsername;
$password_db = $dbPassword;
$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
);

$pdo = new PDO($dsn, $username, $password_db, $options);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Get the total number of PDF files
$pdfFiles = glob("$directory\\*.pdf");
$totalFiles = count($pdfFiles);

// If no files are found, display a message and exit the script
if ($totalFiles == 0) {
    $noFiles = 1;
    //exit;
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

