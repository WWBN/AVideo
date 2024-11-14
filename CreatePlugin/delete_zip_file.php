<?php
require_once __DIR__.'/../videos/configuration.php';

header('Content-Type: application/json');

if(!User::isAdmin()){
    forbiddenPage('You Must be admin');
}

if(!empty($global['disableAdvancedConfigurations'])){
    forbiddenPage('Configuration disabled');
}

$response = ['error' => false, 'msg' => ''];

if (!isset($_POST['fileName'])) {
    $response['error'] = true;
    $response['msg'] = 'File name not specified.';
    echo json_encode($response);
    exit;
}

$fileName = basename($_POST['fileName']); // Sanitize file name
$filePath = __DIR__ . '/plugins/' . $fileName;

if (file_exists($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'zip') {
    if (unlink($filePath)) {
        $response['msg'] = 'File deleted successfully.';
    } else {
        $response['error'] = true;
        $response['msg'] = 'Failed to delete the file.';
    }
} else {
    $response['error'] = true;
    $response['msg'] = 'File does not exist or is not a zip file.';
}

echo json_encode($response);
