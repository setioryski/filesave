<?php
if (isset($_GET['file']) && isset($_GET['dir'])) {
    $file = $_GET['file'];
    $current_directory = $_GET['dir'];

    // Debug: Print the values to ensure they are being passed correctly
    echo "File: $file<br>";
    echo "Directory: $current_directory<br>";

    if (file_exists($file) && !is_dir($file)) {
        unlink($file);
        // Redirect back to the current directory
        header("Location: index.php?dir=" . urlencode($current_directory));
        exit;
    } else {
        echo "File does not exist or cannot be deleted.";
    }
} else {
    echo "No file or directory specified.";
}
?>
