<?php
require_once __DIR__ . '/../../videos/configuration.php';

if (!User::isLogged()) {
    die(json_encode(['error' => true, 'msg' => 'You must be logged in to upload images']));
}

$videoId = intval($_POST['videos_id']); // Get the video ID from the POST data
$users_id = User::getId(); // Get the current user's ID

if ($videoId <= 0) {
    die(json_encode(['error' => true, 'msg' => 'Invalid video ID']));
}

$targetDir = "{$global['systemRootPath']}videos/uploads/comments/{$videoId}/";
make_path($targetDir);
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxFileSize = 10 * 1024 * 1024; // 10 MB

if (!empty($_FILES['comment_image']) && $_FILES['comment_image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['comment_image']['tmp_name'];
    $fileName = basename($_FILES['comment_image']['name']);
    $fileSize = $_FILES['comment_image']['size'];
    $fileType = mime_content_type($fileTmpPath);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Check file size
    if ($fileSize > $maxFileSize) {
        die(json_encode(['error' => true, 'msg' => 'File size exceeds the maximum limit of 10MB']));
    }

    // Check MIME type
    if (!in_array($fileType, $allowedTypes)) {
        die(json_encode(['error' => true, 'msg' => 'Invalid file type. Only JPG, PNG, and GIF files are allowed']));
    }

    // Verify the image using getimagesize
    $imageInfo = getimagesize($fileTmpPath);
    if ($imageInfo === false) {
        die(json_encode(['error' => true, 'msg' => 'Uploaded file is not a valid image']));
    }

    // Check file extension against allowed extensions
    if (!in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
        die(json_encode(['error' => true, 'msg' => 'Invalid file extension. Only JPG, PNG, and GIF files are allowed']));
    }

    // Sanitize file name
    $newFileName = uniqid('comment_img_') . '_user_' . $users_id . '.' . $fileExt;
    $destPath = $targetDir . $newFileName;

    // Move the uploaded file
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        // Set file permissions
        chmod($destPath, 0644);

        $commentText = '![' . pathinfo($fileName, PATHINFO_FILENAME) . '](' . $global['webSiteRootURL'] . 'videos/uploads/comments/' . $videoId . '/' . $newFileName . ')';
        echo json_encode(['error' => false, 'commentText' => $commentText]);
    } else {
        die(json_encode(['error' => true, 'msg' => 'Failed to move the uploaded file']));
    }
} else {
    die(json_encode(['error' => true, 'msg' => 'No file uploaded or there was an upload error']));
}
