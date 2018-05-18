<?php

require_once dirname(__FILE__) . '/../videos/configuration.php';
header('Content-Type: application/json');

if (!User::canUpload() || !empty($advancedCustom->doNotShowImportMP4Button)) {
    return false;
}

$obj = new stdClass();

$obj->error = true;

$obj->fileURI = pathinfo($_POST['fileURI']);

//get description
$filename = $obj->fileURI['dirname'].DIRECTORY_SEPARATOR.$obj->fileURI['filename'];
$extensions = array('txt', 'html', 'htm');

foreach ($extensions as $value) {
    $_POST['description'] = "";
    $_POST['title'] = "";
    if(file_exists("{$filename}.{$value}")){
        $html = file_get_contents("{$filename}.{$value}");
        $breaks = array("<br />","<br>","<br/>");  
        $html = str_ireplace($breaks, "\r\n", $html);
        $_POST['description'] = $html;
        $cleanHTML = strip_tags($html);
        $_POST['title'] = substr($cleanHTML, 0, $_POST['length']);
        break;
    }
}

$tmpDir = sys_get_temp_dir();
$tmpFileName = $tmpDir.DIRECTORY_SEPARATOR.$obj->fileURI['filename'];
$source = $obj->fileURI['dirname'].DIRECTORY_SEPARATOR.$obj->fileURI['basename'];

if (!copy($source, $tmpFileName)) {
    $obj->msg = "failed to copy $filename...\n";
    die(json_encode($obj));
}

if(!empty($_POST['delete']) && $_POST['delete']!=='false'){
    if(is_writable($source)){
        unlink($source);
        foreach ($extensions as $value) {
            if(file_exists("{$filename}.{$value}")){
                unlink("{$filename}.{$value}");
            }
        }
    }else{
        $obj->msg = "Could not delete $source...\n";
    }
}

$_FILES['upl']['error'] = 0;
$_FILES['upl']['name'] = $obj->fileURI['basename'];
$_FILES['upl']['tmp_name'] = $tmpFileName;
$_FILES['upl']['type'] = "video/mp4";
$_FILES['upl']['size'] = filesize($tmpFileName);

require_once $global['systemRootPath'] . 'view/mini-upload-form/upload.php';


echo json_encode($obj);