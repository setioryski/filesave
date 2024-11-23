<?php
// Make sure GET param 'file' exists and the file is valid
if (isset($_GET['file']) && is_file($_GET['file'])) {
    // If form is submitted
    if (isset($_POST['filename'])) {
        // Make sure the filename is valid (excluding special characters except hyphens, dots, and whitespaces)
        if (preg_match('/^[\w\-. ]+$/', $_POST['filename'])) {
            // Construct the new file path
            $new_file_path = rtrim(pathinfo($_GET['file'], PATHINFO_DIRNAME), '/') . '/' . $_POST['filename'];

            // Rename the file
            if (rename($_GET['file'], $new_file_path)) {
                // Redirect to the index page with the current directory
                header('Location: index.php?dir=' . urlencode(dirname($_GET['file'])));
                exit;
            } else {
                exit('Failed to rename the file.');
            }
        } else {
            exit('Please enter a valid name!');
        }
    }
} else {
    exit('Invalid file!');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1">
    <title>Rename File</title>
    <link href="style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body>
    <div class="file-manager">
        <div class="file-manager-header">
            <h1>Rename</h1>
        </div>
        <form action="" method="post">
            <label for="filename">Name</label>
            <input id="filename" name="filename" type="text" placeholder="Name" value="<?= htmlspecialchars(basename($_GET['file']), ENT_QUOTES) ?>" required>
            <button type="submit">Save</button>
        </form>
    </div>
</body>
</html>
