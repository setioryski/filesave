<?php
// Default directory if not set in the URL or form
$current_directory = isset($_GET['dir']) ? rtrim($_GET['dir'], '/') . '/' : 'uploads/';

// Check if current directory exists, if not, create it
if (!is_dir($current_directory)) {
    if (mkdir($current_directory, 0777, true)) {
        echo "Directory created successfully.";
    } else {
        echo "Failed to create directory.";
    }
} else {
    echo "Directory already exists.";
}

// Path where folders should be created (you can adjust this path as needed)
$initial_directory = 'uploads/';
$current_directory = isset($_GET['dir']) ? rtrim($_GET['dir'], '/') . '/' : $initial_directory;

// Function to sanitize the folder name and make sure it's valid
function sanitize_folder_name($folder_name) {
    return preg_replace('/[^a-zA-Z0-9-_]/', '', $folder_name); // Only allow letters, numbers, dashes, and underscores
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get folder name from the form and sanitize it
    $folder_name = sanitize_folder_name(trim($_POST['folder_name']));
    
    // Validate folder name
    if (empty($folder_name)) {
        $error_message = "Folder name cannot be empty!";
    } elseif (preg_match('/[\/\\?%*:|"<>]/', $folder_name)) {
        // Invalid characters
        $error_message = "Folder name contains invalid characters!";
    } else {
        // Path where the new folder will be created
        $folder_path = $current_directory . $folder_name;

        // Debugging output: check the folder path
        echo "Trying to create folder at: " . htmlspecialchars($folder_path);

        // Check if folder already exists
        if (is_dir($folder_path)) {
            $error_message = "Folder '$folder_name' already exists!";
        } else {
            // Try to create the folder
            if (mkdir($folder_path, 0777, true)) {
                // Folder creation was successful, redirect
                header("Location: index.php?dir=" . urlencode($current_directory)); // Redirect back to the file manager
                exit;
            } else {
                // Error creating folder
                $error_message = "Failed to create folder. Check folder permissions.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create New Folder</title>
    <link href="style.css" rel="stylesheet" type="text/css">
</head>

<body>
    <div class="container">
        <div class="file-manager">
            <h2>Create New Folder</h2>

            <!-- Display error message if any -->
            <?php if (isset($error_message)): ?>
                <div class="error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form method="POST" action="folder_create.php?dir=<?= urlencode($current_directory) ?>">
                <label for="folder_name">Folder Name:</label>
                <input type="text" id="folder_name" name="folder_name" required placeholder="Enter folder name">
                <button type="submit" class="btn">Create Folder</button>
            </form>

            <a href="index.php?dir=<?= urlencode($current_directory) ?>" class="btn">Back to File Manager</a>
        </div>
    </div>
</body>

</html>
