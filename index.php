<?php
// Start output buffering
ob_start();

// Error reporting (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initial directory path
$initial_directory = 'uploads/';

// Determine the current directory path
$current_directory = isset($_GET['dir']) ? rtrim($_GET['dir'], '/') . '/' : $initial_directory;

// Include authentication if necessary
include 'authenticate.php';

// Function to secure the path and prevent traversal
function secure_path($path, $initial_directory) {
    // If path does not start with initial_directory, prepend it
    if (strpos($path, $initial_directory) !== 0) {
        $path = $initial_directory . ltrim($path, '/');
    }
    // Realpath to get the absolute path
    $real_initial = realpath($initial_directory);
    $real_path = realpath($path);
    if ($real_path === false || strpos($real_path, $real_initial) !== 0) {
        return $real_initial . DIRECTORY_SEPARATOR;
    }
    // Add DIRECTORY_SEPARATOR if missing
    if (is_dir($real_path) && substr($real_path, -1) !== DIRECTORY_SEPARATOR) {
        $real_path .= DIRECTORY_SEPARATOR;
    }
    return $real_path;
}

// Handle file download requests
if (isset($_GET['file']) && isset($_GET['download'])) {
    $file_path = secure_path($_GET['file'], $initial_directory);
    // Download file if valid
    if (!is_dir($file_path) && strpos($file_path, realpath($initial_directory)) === 0 && file_exists($file_path)) {
        // Headers for download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    } else {
        // Redirect back if file is invalid
        header("Location: index.php?dir=" . urlencode($current_directory));
        exit;
    }
}

// Use glob to get list of files and directories
$results = glob($current_directory . '*');

// Debugging: Display current directory and glob() results
echo "<!-- Current Directory: " . htmlspecialchars($current_directory, ENT_QUOTES) . " -->";
echo "<!-- glob() Result: " . print_r($results, true) . " -->";

// If glob() fails or finds no files, set $results as empty array
if ($results === false || empty($results)) {
    $results = [];
}

// Determine whether directories are listed first
$directory_first = true;

// Sort files if necessary
if ($directory_first) {
    usort($results, function ($a, $b) {
        $a_is_dir = is_dir($a);
        $b_is_dir = is_dir($b);
        if ($a_is_dir === $b_is_dir) {
            return strnatcasecmp($a, $b);
        } else if ($a_is_dir && !$b_is_dir) {
            return -1;
        } else if (!$a_is_dir && $b_is_dir) {
            return 1;
        }
    });
}

// Function to determine file type icon
function get_filetype_icon($filetype)
{
    if (is_dir($filetype)) {
        return '<i class="fa-solid fa-folder"></i>';
    } else {
        $mime = mime_content_type($filetype);
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
<!DOCTYPE html>
<html lang="id">

<head>
    <!-- Meta tags and title -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>File Sharing</title>

    <!-- Link to external CSS files -->
    <link href="style.css" rel="stylesheet" type="text/css">
    <!-- You can remove the following line if not using separate mobile styles -->
    <link href="style-mobile.css" rel="stylesheet" type="text/css" media="only screen and (max-width: 600px)">

    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css"
        integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="#"><i class="fa-solid fa-house"></i> Beranda</a>
        <a href="#"><i class="fa-solid fa-folder"></i> Dokumen</a>
        <a href="#"><i class="fa-solid fa-download"></i> Unduhan</a>
        <a href="#"><i class="fa-solid fa-image"></i> Gambar</a>
        <!-- Add more sidebar links if needed -->
    </div>

    <!-- File Manager -->
    <div class="file-manager">

<!-- Header -->
<div class="file-manager-header">
    <!-- Upload Button -->
    <a href="upload.php" class="btn upload-btn"><i class="fa-solid fa-upload"></i> Upload</a>
    
    <!-- Emoji or Other Action Buttons -->
    <button class="emoji-btn" onclick="window.location.href='viewer.php';"><span>🚨</span></button>
</div>


        <!-- File Table -->
        <table class="file-manager-table">
            <thead>
                <tr>
                    <th class="name-column">Nama <i class="fa-solid fa-arrow-down-long fa-xs"></i></th>
                    <th class="actions-column">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($current_directory != $initial_directory): ?>
                <tr>
                    <td class="name">
                        <i class="fa-solid fa-folder"></i>
                        <a href="?dir=<?= urlencode(dirname(rtrim($current_directory, '/'))) ?>">..</a>
                    </td>
                    <td class="actions"></td>
                </tr>
                <?php endif; ?>
                <?php
                if (!empty($results)) {
                    foreach ($results as $result):
                        $is_dir = is_dir($result);
                        $mime_type = mime_content_type($result);
                        $is_image = preg_match('/^image\//', $mime_type);
                        $is_audio = preg_match('/^audio\//', $mime_type);
                        $is_video = preg_match('/^video\//', $mime_type);
                        ?>
                <tr class="file">
<td class="name">
    <div class="file-item">
        <?= get_filetype_icon($result) ?>
        <?php if ($is_dir): ?>
            <a class="view-directory" href="?dir=<?= urlencode($result) ?>">
                <?= basename($result) ?>
            </a>
        <?php else: ?>
            <a class="view-file"
                href="#"
                data-file="<?= htmlspecialchars($result, ENT_QUOTES) ?>"
                data-type="<?= $is_image ? 'image' : ($is_audio ? 'audio' : ($is_video ? 'video' : 'other')) ?>">
                <?= basename($result) ?>
            </a>
        <?php endif; ?>
    </div>
</td>

                    <td class="actions">
                        <?php if (!$is_dir): ?>
                        <!-- Download Button -->
                        <a href="?dir=<?= urlencode($current_directory) ?>&file=<?= urlencode($result) ?>&download=true"
                            class="btn green" title="Unduh">
                            <i class="fa-solid fa-download fa-xs"></i>
                        </a>
                        <!-- Edit (Rename) Button -->
                        <a href="rename.php?file=<?= urlencode($result) ?>" class="btn blue" title="Ubah Nama">
                            <i class="fa-solid fa-pen-to-square fa-xs"></i>
                        </a>
                        <!-- Delete Button -->
                        <a href="delete.php?file=<?= urlencode($result) ?>&dir=<?= urlencode($current_directory) ?>"
                            class="btn red" title="Hapus"
                            onclick="return confirm('Apakah Anda yakin ingin menghapus file ini?');">
                            <i class="fa-solid fa-trash fa-xs"></i>
                        </a>
                        <?php else: ?>
                        <!-- Placeholder for directories without actions -->
                        <span class="no-actions"></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
                    endforeach;
                } else {
                    echo "<tr><td colspan='2'>Tidak ada file atau direktori.</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div>

    <!-- Media Modal -->
    <div id="media-modal" class="modal">
        <span class="close" onclick="closeMediaModal()">&times;</span>
        <div class="modal-content">
            <!-- Media content will be inserted here by JavaScript -->
        </div>
        <div id="caption" class="caption"></div>
    </div>

    <!-- JavaScript -->
    <script>
        // Function to open media modal
        function openMediaModal(type, src, alt) {
            var modal = document.getElementById('media-modal');
            var container = document.querySelector('.modal-content');
            var captionText = document.getElementById('caption');

            // Clear previous content
            container.innerHTML = '';

            if (type === 'image') {
                var img = document.createElement('img');
                img.src = src;
                img.alt = alt;
                container.appendChild(img);
            } else if (type === 'audio') {
                var audio = document.createElement('audio');
                audio.controls = true;
                audio.autoplay = true;
                var source = document.createElement('source');
                source.src = src;
                source.type = 'audio/mpeg';
                audio.appendChild(source);
                container.appendChild(audio);
            } else if (type === 'video') {
                var video = document.createElement('video');
                video.controls = true;
                video.autoplay = true;
                var source = document.createElement('source');
                source.src = src;
                source.type = 'video/mp4';
                video.appendChild(source);
                container.appendChild(video);
            } else {
                // For other file types, do not display modal
                return;
            }

            captionText.innerHTML = alt;
            modal.style.display = "flex"; // Use flex to align properly
        }

        // Function to close media modal
        function closeMediaModal() {
            var modal = document.getElementById('media-modal');
            var container = document.querySelector('.modal-content');
            var captionText = document.getElementById('caption');

            // Stop any playing media
            var mediaElements = container.querySelectorAll('audio, video');
            mediaElements.forEach(function(media) {
                media.pause();
                media.currentTime = 0;
            });

            // Clear content
            container.innerHTML = '';
            captionText.innerHTML = '';

            modal.style.display = "none";
        }

        // Add event listeners to file links after DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            var fileLinks = document.querySelectorAll('.view-file');
            fileLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent default link action
                    var fileSrc = link.getAttribute('data-file');
                    var fileType = link.getAttribute('data-type');
                    var altText = link.textContent;

                    // Adjust relative path as needed
                    var relativePath = fileSrc.startsWith('/') ? fileSrc : fileSrc;

                    // Determine media type
                    if (fileType === 'image' || fileType === 'audio' || fileType === 'video') {
                        openMediaModal(fileType, relativePath, altText);
                    } else {
                        // For other file types, perform default action
                        window.location.href = fileSrc;
                    }
                });
            });
        });

        // Close modal when clicking outside the content
        window.onclick = function(event) {
            var modal = document.getElementById('media-modal');
            if (event.target == modal) {
                closeMediaModal();
            }
        }

        // Optional: Close modal on Esc key press for better accessibility
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeMediaModal();
            }
        });
    </script>
</body>

</html>
