<?php
$configFile = '../../videos/configuration.php';
if (!file_exists($configFile)) {
    $configFile = '../videos/configuration.php';
}
require_once $configFile;

if(!User::isLogged()){
    die('{"status":"error", "msg":"Only logged users can upload"}');
}
echo "Success: login OK\n";

header('Content-Type: application/json');

// A list of permitted file extensions
$allowed = array('mp4', 'avi', 'mov','mkv', 'flv', 'mp3', 'wav');

if(isset($_FILES['upl']) && $_FILES['upl']['error'] == 0){

    echo "Success: \$_FILES OK\n";
	$extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);

	if(!in_array(strtolower($extension), $allowed)){
		echo '{"status":"error", "msg":"File extension error ['.$_FILES['upl']['name'].'], we allow only ('. implode(",", $allowed).')"}';
		exit;
	}
        
    echo "Success: file extension OK\n";
        
        //chack if is an audio
        $type = "";
        if(strcasecmp($extension, 'mp3') == 0 || strcasecmp($extension, 'wav') == 0){
            $type = 'audio';
        }
        //var_dump($extension, $type);exit;
        
        require_once $global['systemRootPath'] . 'objects/video.php';
        
        echo "Starting Get Duration\n";
        $duration = Video::getDurationFromFile($_FILES['upl']['tmp_name']);
                
        $path_parts = pathinfo( $_FILES['upl']['name']);        
        $mainName = preg_replace("/[^A-Za-z0-9]/", "",$path_parts['filename']);
        $filename = uniqid($mainName."_", true);
                
        $video = new Video($_FILES['upl']['name'], $filename);
        $video->setDuration($duration);
        if($type=='audio'){
            $video->setType($type);
        }else{
            $video->setType("video");
        }
        $id = $video->save();
        /**
         * This is when is using in a non uploaded movie
         */
        if(!empty($_FILES['upl']['dontMoveUploadedFile'])){
            rename($_FILES['upl']['tmp_name'], "{$global['systemRootPath']}videos/original_".$filename);
        }else if(move_uploaded_file($_FILES['upl']['tmp_name'], "{$global['systemRootPath']}videos/original_".$filename)){
            // convert video
            $cmd = "/usr/bin/php -f videoEncoder.php {$filename} {$id} {$type} > /dev/null 2>/dev/null &";
            echo "** executing command {$cmd}\n";
            exec($cmd);
	}else{
            die("Error on move_uploaded_file(".$_FILES['upl']['tmp_name'].", "."{$global['systemRootPath']}videos/original_".$filename.")");
        }
        
        
        //exec("/usr/bin/php -f videoEncoder.php {$_FILES['upl']['tmp_name']} {$filename}  1> {$global['systemRootPath']}videos/{$filename}_progress.txt  2>&1", $output, $return_val);
        //var_dump($output, $return_val);
        
        echo '{"status":"success", "msg":"Your video ('.$filename.') is encoding <br> '.$cmd.'", "filename":"'.$filename.'", "duration":"'.$duration.'"}';
        exit;
}

echo '{"status":"error", "msg":'.json_encode($_FILES).', "type":"$_FILES Error"}';
exit;