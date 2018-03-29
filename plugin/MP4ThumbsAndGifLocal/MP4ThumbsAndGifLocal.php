<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class MP4ThumbsAndGifLocal extends PluginAbstract {

    public function getDescription() {
        return "This is a standalone-thumbnailer.<br />You need to have imagemagick (convert-command must be avaible) and ffmpeg installed to make this working.
        <ul><li><b>videoStartTime:</b> Time in the video where the thumb is created and the gif start.</li>
        </ul>";                                                                                             
    }                                                                                                                                                                                                                                       
                                                                                                                                                                                                                                            
    public function getName() {                                                                                                                                                                                                             
        return "MP4ThumbsAndGifLocal";                                                                                                                                                                                                           
    }                                                                                                                                                                                                                                       
                                                                                                                                                                                                                                            
    public function getUUID() {                                                                                                                                                                                                             
        return "916c9afb-css90e-26fa-97fd-864856180cc9";                                                                                                                                                                                      
    }                                                                                                                                                                                                                                       
                                                                                                                                                                                                                                            
    public function getEmptyDataObject() {                                                                                                                                 
        global $global;
        $obj = new stdClass();
        $obj->videoStartTime = "00:03:00";
        $obj->gifFrames = 10;
        $obj->gifDelay = 25;
        return $obj;
    }
        
    public static function getImage($filename, $type){                
        $obj = YouPHPTubePlugin::getObjectData("MP4ThumbsAndGifLocal");
        if($type=='jpg'){
            exec("rm -rf ../../plugin/MP4ThumbsAndGifLocal/cache/*");
            exec("ffmpeg -i ../../videos/".$filename.".mp4 -vf scale=210:118 -r 0.1 -ss ".$obj->videoStartTime." -vframes ".$obj->gifFrames." '../../plugin/MP4ThumbsAndGifLocal/cache/frame-%03d.jpg' 2>&1", $out);
            exec("cp ../../plugin/MP4ThumbsAndGifLocal/cache/frame-001.jpg ../../videos/".$filename.".jpg");
        } else {
            exec("convert -delay ".$obj->gifDelay." -loop 0 ../../plugin/MP4ThumbsAndGifLocal/cache/*.jpg ../../videos/".$filename.".gif");
        }
    }   
    
    public function getTags() {
        return array('free', 'thumbs', 'gif');
    }

    
}
