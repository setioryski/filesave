<?php
// Database connection
$servername = "localhost";
$username = "polc8288_polmor";
$password = "3a&2JB}JORFe";
$dbname = "polc8288_stickynotes";
$max_retries = 3;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to execute a query with retry mechanism
function executeQuery($conn, $stmt, $max_retries) {
    global $servername, $username, $password, $dbname;
    $attempt = 0;
    while ($attempt < $max_retries) {
        try {
            if ($stmt->execute()) {
                return true;
            } else {
                throw new Exception("Error executing query: " . $stmt->error);
            }
        } catch (mysqli_sql_exception $e) {
            if ($conn->errno == 2006 || $conn->errno == 2013) {
                // MySQL server has gone away or lost connection
                $attempt++;
                $conn->close();
                $conn = new mysqli($servername, $username, $password, $dbname);
                $stmt = $conn->prepare($stmt->query);
            } else {
                throw $e;
            }
        }
    }
    return false;
}

// Function to make links clickable
function makeLinksClickable($text) {
    $pattern = '/(https?:\/\/[^\s]+)/';
    $replacement = '<a href="$1" target="_blank">$1</a>';
    return preg_replace($pattern, $replacement, $text);
}

// Add a new note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $stmt = $conn->prepare("INSERT INTO notes (title, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $content);
    if (!executeQuery($conn, $stmt, $max_retries)) {
        die("Error: Unable to add note after multiple attempts");
    }
}

// Edit a note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $stmt = $conn->prepare("UPDATE notes SET title=?, content=? WHERE id=?");
    $stmt->bind_param("ssi", $title, $content, $id);
    if (!executeQuery($conn, $stmt, $max_retries)) {
        die("Error: Unable to edit note after multiple attempts");
    }
}

// Delete a note
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM notes WHERE id=?");
    $stmt->bind_param("i", $id);
    if (!executeQuery($conn, $stmt, $max_retries)) {
        die("Error: Unable to delete note after multiple attempts");
    }
}

// Retrieve all notes
$result = $conn->query("SELECT * FROM notes ORDER BY created_at DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POLISI NOTES</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            align-items: center;
            margin-top: 20px;
        }
        .header img {
            margin-left: 10px;
            width: 50px;
            height: 50px;
        }
        h1 {
            color: #333;
        }
        form {
            margin: 20px 0;
            width: 90%;
            max-width: 600px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            background-color: white;
            border-radius: 10px;
        }
        input[type="text"], textarea {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
            resize: none;
            font-size: 16px;
        }
        button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #0288d1;
            color: white;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0277bd;
        }
        .notes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
            width: 90%;
            max-width: 1200px;
            padding: 0 10px;
        }
        .note {
            background: #fff;
            padding: 15px;
            margin: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            position: relative;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .note:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .note h2, .note p {
            margin: 0;
            word-wrap: break-word;
            font-size: 16px;
        }
        .note h2[contenteditable], .note p[contenteditable] {
            border: 1px solid #007bff;
            padding: 5px;
        }
        .note .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .note .delete, .note .copy, .note .edit, .note .save, .note .cancel {
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            text-align: center;
            font-size: 14px;
            margin-right: 5px;
        }
        .note .delete {
            background-color: #e53935;
            color: white;
        }
        .note .delete:hover {
            background-color: #d32f2f;
        }
        .note .copy {
            background-color: #8e24aa;
            color: white;
        }
        .note .copy:hover {
            background-color: #7b1fa2;
        }
        .note .edit {
            background-color: #fdd835;
            color: black;
        }
        .note .edit:hover {
            background-color: #fbc02d;
        }
        .note .save {
            background-color: #43a047;
            color: white;
            display: none;
        }
        .note .save:hover {
            background-color: #388e3c;
        }
        .note .cancel {
            background-color: #e53935;
            color: white;
            display: none;
        }
        .note .cancel:hover {
            background-color: #d32f2f;
        }
        .copy-message {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #43a047;
            color: white;
            padding: 10px;
            border-radius: 5px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPOR AMORAL</h1>
        <img src="/siren.gif" alt="Siren GIF">
    </div>
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <input type="text" name="title" placeholder="Apa laporan kamu..." required>
        <textarea name="content" rows="4" placeholder="Tulis keluhan kamu..." required></textarea>
        <button type="submit">LAPOR!</button>
    </form>
    <div class="notes">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="note" id="note-<?php echo $row['id']; ?>">
                <h2 contenteditable="false" id="title-<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></h2>
                <p contenteditable="false" id="content-<?php echo $row['id']; ?>"><?php echo nl2br(makeLinksClickable(htmlspecialchars($row['content']))); ?></p>
                <div class="actions">
                    <span class="copy" onclick="copyToClipboard('content-<?php echo $row['id']; ?>')">Copy</span>
                    <a class="edit" href="javascript:void(0)" onclick="editNote('<?php echo $row['id']; ?>')">Edit</a>
                    <a class="save" href="javascript:void(0)" onclick="saveNote('<?php echo $row['id']; ?>')">Save</a>
                    <a class="cancel" href="javascript:void(0)" onclick="cancelEdit('<?php echo $row['id']; ?>')">Cancel</a>
                    <a class="delete" href="?delete=<?php echo $row['id']; ?>">Delete</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <div class="copy-message" id="copyMessage">Note copied to clipboard!</div>
    <script>
        function copyToClipboard(elementId) {
            const content = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(content)
                .then(showCopyMessage)
                .catch(err => console.error('Could not copy text: ', err));
        }

        function showCopyMessage() {
            const message = document.getElementById('copyMessage');
            message.style.display = 'block';
            setTimeout(() => {
                message.style.display = 'none';
            }, 2000);
        }

        function editNote(id) {
            const titleElement = document.getElementById('title-' + id);
            const contentElement = document.getElementById('content-' + id);
            titleElement.setAttribute('contenteditable', 'true');
            contentElement.setAttribute('contenteditable', 'true');
            titleElement.setAttribute('data-original', titleElement.innerText);
            contentElement.setAttribute('data-original', contentElement.innerText);
            const noteElement = document.getElementById('note-' + id);
            noteElement.querySelector('.edit').style.display = 'none';
            noteElement.querySelector('.save').style.display = 'inline-block';
            noteElement.querySelector('.cancel').style.display = 'inline-block';
        }

        function cancelEdit(id) {
            const titleElement = document.getElementById('title-' + id);
            const contentElement = document.getElementById('content-' + id);
            titleElement.setAttribute('contenteditable', 'false');
            contentElement.setAttribute('contenteditable', 'false');
            titleElement.innerText = titleElement.getAttribute('data-original');
            contentElement.innerText = contentElement.getAttribute('data-original');
            const noteElement = document.getElementById('note-' + id);
            noteElement.querySelector('.edit').style.display = 'inline-block';
            noteElement.querySelector('.save').style.display = 'none';
            noteElement.querySelector('.cancel').style.display = 'none';
        }

        function saveNote(id) {
            const titleElement = document.getElementById('title-' + id);
            const contentElement = document.getElementById('content-' + id);
            const title = titleElement.innerText;
            const content = contentElement.innerText;
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 'action': 'edit', 'id': id, 'title': title, 'content': content })
            })
            .then(response => response.text())
            .then(() => {
                titleElement.setAttribute('contenteditable', 'false');
                contentElement.setAttribute('contenteditable', 'false');
                const noteElement = document.getElementById('note-' + id);
                noteElement.querySelector('.edit').style.display = 'inline-block';
                noteElement.querySelector('.save').style.display = 'none';
                noteElement.querySelector('.cancel').style.display = 'none';
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>
