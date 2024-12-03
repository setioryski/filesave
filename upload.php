<?php
// upload.php

// Include the common configuration and functions
include 'config.php';

// Get the current directory
$current_directory = get_current_directory($initial_directory);

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['files'])) {
    $response = array('success' => false, 'messages' => array());
    $files = $_FILES['files'];
    $total_files = count($files['name']);

    for ($i = 0; $i < $total_files; $i++) {
        $filename = basename($files['name'][$i]);
        $targetFile = rtrim($current_directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($targetFile)) {
            $response['messages'][] = "Maaf, file $filename sudah ada.";
        } else {
            if (move_uploaded_file($files['tmp_name'][$i], $targetFile)) {
                $response['messages'][] = "File $filename berhasil diunggah.";
            } else {
                $response['messages'][] = "Maaf, terjadi kesalahan saat mengunggah $filename.";
                $response['messages'][] = "Kode error: " . $files['error'][$i];
            }
        }
    }

    $response['success'] = true;

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Determine the last directory name for display
$last_directory = basename(rtrim($current_directory, DIRECTORY_SEPARATOR));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unggah File</title>
    <link href="styleupload.css" rel="stylesheet" type="text/css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        .drop-zone {
            width: 100%;
            height: 200px;
            border: 2px dashed #cccccc;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #cccccc;
            margin-top: 20px;
            cursor: pointer;
        }
        .drop-zone.dragover {
            background-color: #f0f0f0;
            border-color: #333333;
            color: #333333;
        }
        .hidden {
            display: none;
        }
        .progress-bar-container {
            width: 100%;
            background-color: #f3f3f3;
            margin-top: 20px;
            border-radius: 5px;
        }
        .progress-bar {
            width: 0%;
            height: 20px;
            background-color: #4caf50;
            text-align: center;
            line-height: 20px;
            color: white;
            border-radius: 5px;
        }
        .upload-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #333;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="upload-container">
    <h1>Unggah File ke <?= htmlspecialchars($last_directory) ?></h1>
    <form id="uploadForm" action="upload.php?dir=<?= urlencode($current_directory) ?>" method="post" enctype="multipart/form-data">
        <label for="fileToUpload">Pilih file untuk diunggah:</label>
        <input type="file" name="files[]" id="fileToUpload" multiple required>
        <div class="drop-zone" id="drop-zone">
            Drag & Drop Files Here
        </div>
        <input type="submit" value="Unggah File" name="submit">
    </form>
    <div id="loading" class="hidden">Mengunggah...</div>
    <div class="progress-bar-container hidden" id="progress-bar-container">
        <div class="progress-bar" id="progress-bar">0%</div>
    </div>
    <div id="result" class="hidden"></div>
    <a href="index.php?dir=<?= urlencode($current_directory) ?>" class="back-link">Kembali ke Pengelola File</a>
</div>

<script>
    $(document).ready(function() {
        function handleFiles(files) {
            var formData = new FormData();
            for (var i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
            uploadFiles(formData);
        }

        function uploadFiles(formData) {
            var xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    var percentComplete = (e.loaded / e.total) * 100;
                    $('#progress-bar').css('width', percentComplete + '%').text(Math.round(percentComplete) + '%');
                }
            });

            xhr.upload.addEventListener('loadstart', function() {
                $('#loading').removeClass('hidden');
                $('#progress-bar-container').removeClass('hidden');
                $('#result').addClass('hidden').text('');
            });

            xhr.upload.addEventListener('loadend', function() {
                $('#loading').addClass('hidden');
            });

            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        $('#result').removeClass('hidden').html('');
                        response.messages.forEach(function(message) {
                            if (response.success) {
                                $('#result').css('color', 'green').append('<p>' + message + '</p>');
                            } else {
                                $('#result').css('color', 'red').append('<p>' + message + '</p>');
                            }
                        });

                        if (response.success) {
                            $('#progress-bar-container').addClass('hidden');
                        }
                    } else {
                        console.error('Error: ' + xhr.statusText);
                        $('#result').css('color', 'red').text('Error: ' + xhr.statusText).removeClass('hidden');
                    }
                }
            };

            xhr.onerror = function() {
                console.error('Network Error');
                $('#result').css('color', 'red').text('Network Error').removeClass('hidden');
            };

            xhr.ontimeout = function() {
                console.error('Request Timed Out');
                $('#result').css('color', 'red').text('Request Timed Out').removeClass('hidden');
            };

            xhr.open('POST', $('#uploadForm').attr('action'), true);
            xhr.send(formData);
        }

        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();  // Prevent the default form submission
            var files = $('#fileToUpload')[0].files;
            handleFiles(files);
        });

        var dropZone = $('#drop-zone');

        dropZone.on('dragover', function(e) {
            e.preventDefault();
            dropZone.addClass('dragover');
        });

        dropZone.on('dragleave', function(e) {
            e.preventDefault();
            dropZone.removeClass('dragover');
        });

        dropZone.on('drop', function(e) {
            e.preventDefault();
            dropZone.removeClass('dragover');
            var files = e.originalEvent.dataTransfer.files;
            handleFiles(files);
        });

        // Optional: Click on drop zone to open file dialog
        dropZone.on('click', function() {
            $('#fileToUpload').click();
        });
    });
</script>
</body>
</html>
