<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if(!User::isLogged()){
    die('{"status":"error", "msg":"Only logged users can upload"}');
}

header('Content-Type: application/json');

// A list of permitted file extensions
$allowed = array('mp4', 'avi', 'mov', 'flv', 'mp3', 'wav');

if(isset($_FILES['upl']) && $_FILES['upl']['error'] == 0){

	$extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);

	if(!in_array(strtolower($extension), $allowed)){
		echo '{"status":"error", "msg":"File extension error, we allow only ('. implode(",", $allowed).')"}';
		exit;
	}
        
        
        //chack if is an audio
        $type = "";
        if(strcasecmp($extension, 'mp3') == 0 || strcasecmp($extension, 'wav') == 0){
            $type = 'audio';
        }
        //var_dump($extension, $type);exit;
        
        require_once $global['systemRootPath'] . 'objects/video.php';
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
        
        if(move_uploaded_file($_FILES['upl']['tmp_name'], "{$global['systemRootPath']}videos/original_".$filename)){
            // convert video
            $cmd = "/usr/bin/php -f videoEncoder.php {$filename} {$id} {$type} > /dev/null 2>/dev/null &";
            exec($cmd);
	}
        
        
        //exec("/usr/bin/php -f videoEncoder.php {$_FILES['upl']['tmp_name']} {$filename}  1> {$global['systemRootPath']}videos/{$filename}_progress.txt  2>&1", $output, $return_val);
        //var_dump($output, $return_val);
        
        echo '{"status":"success", "msg":"Your video ('.$filename.') is encoding <br> '.$cmd.'", "filename":"'.$filename.'", "duration":"'.$duration.'"}';
        exit;
}

echo '{"status":"error", "msg":'.json_encode($_FILES).'}';
exit;