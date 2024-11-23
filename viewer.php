<?php
// Function to retrieve image files from a directory
function get_image_files($directory) {
    $files = glob($directory . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    return $files;
}

// Specify the directory to show images from
$image_directory = 'uploads/image/';

// Get image files from the specified directory
$image_files = get_image_files($image_directory);

// Function to get filename without extension from a path
function get_filename($filepath) {
    return pathinfo($filepath, PATHINFO_FILENAME);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjara Amoral</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fafafa;
            display: flex;
            justify-content: center;
            padding: 12px;
        }
        .container {
            max-width: 800px;
            width: 100%;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-color: #f8f8f8;
            border-bottom: 1px solid #ddd;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .header img {
            margin-left: 10px;
            width: 40px;
            height: 40px;
        }
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 4px;
            padding: 10px;
        }
        .gallery-item {
            position: relative;
            overflow: hidden;
            width: 100%;
            padding-top: 100%; /* 1:1 Aspect Ratio */
            cursor: pointer;
            border-radius: 8px;
            transition: transform 0.2s ease-in-out;
        }
        .gallery-item:hover {
            transform: scale(1.02);
        }
        .gallery-item img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensures the image covers the container without distortion */
            display: block;
            transition: transform 0.2s ease-in-out;
            border-radius: 8px;
        }
        .gallery-item:hover img {
            transform: scale(1.1);
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
        }
        .gallery-item.liked .overlay {
            opacity: 1;
        }
        .gallery-item.liked .overlay .fas {
            color: red;
        }
        .filename {
            position: absolute;
            bottom: 4px;
            left: 4px;
            right: 4px;
            color: #fff;
            font-size: 12px;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 2px 6px;
            border-radius: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background-color: #f8f8f8;
            border-top: 1px solid #ddd;
            font-size: 14px;
            color: #666;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.9);
        }
        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
        }
        .modal-content img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
        .modal-filename {
            margin-top: 20px;
            text-align: center;
            color: #fff;
            font-size: 18px;
        }
        .close {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }
        @media (max-width: 600px) {
            .gallery {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            Penjara Amoral
            <img src="/siren.gif" alt="Siren GIF">
        </div>
        <div class="gallery">
            <?php foreach ($image_files as $image): ?>
                <div class="gallery-item" ontouchstart="handleTap(event, '<?= $image ?>', '<?= get_filename($image) ?>')">
                    <img src="<?= $image ?>" alt="<?= get_filename($image) ?>">
                    <div class="overlay">
                        <i class="fas fa-heart"></i> <span>Like</span>
                    </div>
                    <div class="filename"><?= get_filename($image) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="footer">
            &copy; <?= date('Y') ?> Filesave 🚨. All rights reserved.
        </div>
    </div>

    <!-- The Modal -->
    <div id="myModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-content" id="modalContent">
            <img id="modalImage" src="" alt="">
            <div class="modal-filename" id="modalFilename"></div>
        </div>
    </div>

    <script>
        let tapTimeout, tapCount = 0;

        function handleTap(event, imageSrc, filename) {
            tapCount++;
            if (tapCount === 1) {
                tapTimeout = setTimeout(() => {
                    tapCount = 0;
                    toggleLike(event);
                }, 300);
            } else if (tapCount === 2) {
                clearTimeout(tapTimeout);
                tapTimeout = setTimeout(() => {
                    tapCount = 0;
                }, 300);
                showTemporaryRedHeart(event);
            } else if (tapCount === 3) {
                clearTimeout(tapTimeout);
                tapCount = 0;
                showModal(imageSrc, filename);
            }
        }

        function toggleLike(event) {
            const item = event.currentTarget;
            item.classList.toggle('liked');
        }

        function showTemporaryRedHeart(event) {
            const item = event.currentTarget;
            item.classList.add('liked');
            setTimeout(() => {
                item.classList.remove('liked');
            }, 1000); // Keeps the heart red for 1 second
        }

        function showModal(imageSrc, filename) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('modalFilename').textContent = filename;
            document.getElementById('myModal').style.display = "block";
        }

        function closeModal() {
            document.getElementById('myModal').style.display = "none";
        }
    </script>
</body>
</html>
