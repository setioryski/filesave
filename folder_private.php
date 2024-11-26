<?php
session_start(); // Start the session to manage folder access

// Configuration: Define private folders and their required passwords
$private_folders = ['uploads/bahan' => '2143',];

// Trim the trailing slash for consistent comparison
$trimmed_current_directory = rtrim($current_directory, '/');

// Check if the current directory is private
if (array_key_exists($trimmed_current_directory, $private_folders)) {
    // Check if the user has access to this folder
    if (!isset($_SESSION['authenticated'][$trimmed_current_directory])) {
        // If a password is submitted, verify it
        if (isset($_POST['folder_password'])) {
            if ($_POST['folder_password'] === $private_folders[$trimmed_current_directory]) {
                $_SESSION['authenticated'][$trimmed_current_directory] = true; // Grant access
                header("Location: index.php?dir=" . urlencode($current_directory)); // Reload page
                exit;
            } else {
                $error = "Incorrect password!";
            }
        }

        // Show password form if not authenticated
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Password Protected</title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    font-family: Arial, sans-serif;
                }
                .modal {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(139, 0, 0, 0.8); /* Dark red background */
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 1000;
                }
                .modal-content {
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 8px;
                    width: 90%;
                    max-width: 400px;
                    text-align: center;
                    box-shadow: 0 2px 10px rgba(139, 0, 0, 0.4); /* Red shadow */
                }
                .modal-content h3 {
                    margin-top: 0;
                    font-size: 24px;
                    color: #b22222; /* Firebrick red */
                }
                .modal-content p {
                    font-size: 16px;
                    color: #800000; /* Dark red */
                }
                .modal-content form {
                    margin-top: 20px;
                }
                .modal-content input[type="password"] {
                    width: 90%;
                    padding: 12px 15px;
                    margin-bottom: 15px;
                    border: 1px solid #b22222; /* Firebrick red border */
                    border-radius: 4px;
                    font-size: 16px;
                }
                .modal-content button {
                    width: 100%;
                    padding: 12px 15px;
                    background-color: #b22222; /* Firebrick red */
                    border: none;
                    border-radius: 4px;
                    color: #fff;
                    font-size: 16px;
                    cursor: pointer;
                }
                .modal-content button:hover {
                    background-color: #8b0000; /* Darker red */
                }
                .modal-content .error {
                    color: #dc3545; /* Red alert color */
                    margin-top: 10px;
                }
                @media (max-width: 480px) {
                    .modal-content {
                        width: 90%;
                    }
                }
            </style>
        </head>
        <body>
            <div id="password-modal" class="modal">
                <div class="modal-content">
                    <h3>This folder is password protected</h3>
                    <p>Masukan password untuk masuk Ke folder berbahaya ini.</p>
                    <form method="POST">
                        <input type="password" name="folder_password" placeholder="Password" required>
                        <button type="submit">Enter</button>
                        '. (isset($error) ? '<p class="error">' . htmlspecialchars($error) . '</p>' : '') .'
                    </form>
                </div>
            </div>
        </body>
        </html>
        ';
        exit; // Stop further execution until password is entered
    }
}
?>
