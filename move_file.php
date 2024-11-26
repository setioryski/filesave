<?php
// Check if the request method is POST and the required parameters are set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file']) && isset($_POST['target_folder'])) {
    
    // Sanitize inputs
    $file = basename($_POST['file']); // Avoid path traversal issues
    $target_folder = rtrim($_POST['target_folder'], '/') . '/'; // Ensure target folder has trailing slash
    
    // Set the source folder (assuming all files are in "uploads/")
    $source_folder = 'uploads/';
    
    // Construct full file paths
    $source_file = $source_folder . $file;
    $target_file = $target_folder . $file;

    // Check if the source file exists
    if (file_exists($source_file)) {
        
        // Create target folder if it doesn't exist
        if (!is_dir($target_folder)) {
            mkdir($target_folder, 0777, true); // Create directory if it doesn't exist
        }

        // Move the file
        if (rename($source_file, $target_file)) {
            echo "File '$file' has been successfully moved to '$target_folder'.";
        } else {
            echo "Error: Unable to move the file.";
        }
    } else {
        echo "Error: Source file does not exist.";
    }
} else {
    echo "Invalid request.";
}
?>
