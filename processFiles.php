<?php
$target_dir = "c:\\paystubs\\";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Check if the uploaded file is a PDF
if (isset($_FILES["fileToUpload"])) {
    if($fileType == "pdf") {
        $uploadOk = 1;
    } else {
        echo "Sorry, only PDF files are allowed.";
        $uploadOk = 0;
    }
}

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}

// Check file size (limit: 10MB for example)
if ($_FILES["fileToUpload"]["size"] > 10000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Upload file if there were no errors
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) ." has been uploaded.";
        echo "<br/>PROCESSING...<br/>";
?>
        <script>
            window.parent.location.href = window.parent.location.href;
        </script>
<?php
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>
