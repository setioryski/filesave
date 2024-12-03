<?php
// config.php

// Start output buffering
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initial directory path
$initial_directory = 'uploads/';

// Include authentication if necessary
// include 'folder_private.php'; // Uncomment if you have authentication

// Function to secure the path and prevent traversal
function secure_path($path, $initial_directory) {
    // Normalize initial directory path
    $real_initial = realpath($initial_directory);
    if ($real_initial === false) {
        die("Error: Initial directory does not exist.");
    }

    // If no path is provided, return initial directory
    if (empty($path)) {
        return $real_initial . DIRECTORY_SEPARATOR;
    }

    // Resolve the real path
    $real_path = realpath($path);

    // Validate the path
    if ($real_path === false || strpos($real_path, $real_initial) !== 0) {
        return $real_initial . DIRECTORY_SEPARATOR;
    }

    // Add DIRECTORY_SEPARATOR if it's a directory and missing
    if (is_dir($real_path) && substr($real_path, -1) !== DIRECTORY_SEPARATOR) {
        $real_path .= DIRECTORY_SEPARATOR;
    }

    return $real_path;
}

// Function to get current directory based on parameter
function get_current_directory($initial_directory) {
    $dir_param = isset($_GET['dir']) ? $_GET['dir'] : '';
    $current_directory = secure_path($dir_param, $initial_directory);
    return $current_directory;
}

// Function to get file type icon
function get_filetype_icon($file) {
    if (is_dir($file)) {
        return '<i class="fa-solid fa-folder"></i>';
    } else {
        $mime = mime_content_type($file);
        if (preg_match('/^image\//', $mime)) {
            return '<i class="fa-solid fa-file-image"></i>';
        } elseif (preg_match('/^video\//', $mime)) {
            return '<i class="fa-solid fa-file-video"></i>';
        } elseif (preg_match('/^audio\//', $mime)) {
            return '<i class="fa-solid fa-file-audio"></i>';
        }
        return '<i class="fa-solid fa-file"></i>';
    }
}
?>
