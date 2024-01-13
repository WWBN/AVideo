<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
header('Content-Type: application/json');

if (!User::canUpload() || !empty($advancedCustom->doNotShowImportMP4Button)) {
    return false;
}
$global['allowed'] = ['mp4'];
$files = [];
$listedFiles = []; // Array to keep track of files already listed

if (!empty($_POST['path'])) {
    $path = $_POST['path'];
    if (substr($path, -1) !== '/') {
        $path .= "/";
    }

    if (file_exists($path)) {
        $extn = implode(",*.", $global['allowed']);
        $filesStr = "{*." . $extn . ",*." . strtolower($extn) . ",*." . strtoupper($extn) . "}";

        $video_array = glob($path . $filesStr, GLOB_BRACE);

        $id = 0;
        foreach ($video_array as $key => $value) {
            $path_parts = pathinfo($value);
            $filePath = mb_convert_encoding($value, 'UTF-8');
            $fileName = mb_convert_encoding($path_parts['basename'], 'UTF-8');

            // Check if the file has already been listed
            $fileKey = strtolower($fileName); // Convert filename to lower case for comparison
            if (!in_array($fileKey, $listedFiles)) {
                $obj = new stdClass();
                $obj->id = $id++;
                $obj->path = $filePath;
                $obj->name = $fileName;
                $files[] = $obj;
                $listedFiles[] = $fileKey; // Add to listed files array
            }
        }
    }
}
echo json_encode($files);
