<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AI/Objects/Ai_responses.php';
require_once $global['systemRootPath'] . 'plugin/AI/Objects/Ai_metatags_responses.php';
require_once $global['systemRootPath'] . 'plugin/AI/Objects/Ai_transcribe_responses.php';



class AI extends PluginAbstract {
    
    static $isTest = 0;
    static $url = 'https://ai.ypt.me/';
    static $url_test = 'http://192.168.0.2:81/AI/';

    static function getMetadataURL(){
        return self::$isTest?self::$url_test:self::$url;
    }

    static function getPricesURL(){
        return self::$url.'prices.json.php';
    }

    public function getDescription() {
        $desc = "Optimize video visibility with AI-driven meta-tag suggestions and automatic transcription for enhanced SEO performance.";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/AI-Plugin' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        
        //$desc .= $this->isReadyLabel(array('YPTWallet'));
        return $desc.$help;
    }

    public function getName() {
        return "AI";
    }

    public function getUUID() {
        return "AI-5ee8405eaaa16";
    }

    public function getPluginVersion() {
        return "2.0";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->AccessToken = "";
        /*
          $obj->textSample = "text";
          $obj->checkboxSample = true;
          $obj->numberSample = 5;

          $o = new stdClass();
          $o->type = array(0=>__("Default"))+array(1,2,3);
          $o->value = 0;
          $obj->selectBoxSample = $o;

          $o = new stdClass();
          $o->type = "textarea";
          $o->value = "";
          $obj->textareaSample = $o;
         */
        return $obj;
    }

    static function getVideoTranslationMetadata($videos_id, $lang){
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = '';
        $obj->response = array();

        if (empty($videos_id)) {
            $obj->msg = 'empty videos id';
            return $obj;
        }

        if(!Video::canEdit($videos_id)){
            $obj->msg = 'you cannot edit this video';
            return $obj;
        }

        $video = new Video('', '', $videos_id);
        $filename = $video->getFilename();

        if(AVideoPlugin::isEnabledByName('SubtitleSwitcher')){
            SubtitleSwitcher::transcribe($videos_id, false);
        }

        $paths = Ai_transcribe_responses::getVTTPaths($videos_id);

        //var_dump($paths);exit;
        $obj->response = array(
            'type' => 'translation',
            'vtt' => $paths['url'],
            'lang' => $lang
        );

        $obj->error = false;
        return $obj;
    }

    static function getVideoBasicMetadata($videos_id){
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = '';
        $obj->response = array();

        if (empty($videos_id)) {
            $obj->msg = 'empty videos id';
            return $obj;
        }

        if(!Video::canEdit($videos_id)){
            $obj->msg = 'you cannot edit this video';
            return $obj;
        }

        $video = new Video('', '', $videos_id);
        $filename = $video->getFilename();

        if(AVideoPlugin::isEnabledByName('SubtitleSwitcher')){
            SubtitleSwitcher::transcribe($videos_id, false);
        }
        /*
        */
        $paths = Ai_transcribe_responses::getVTTPaths($videos_id);
        //var_dump($paths);exit;
        $obj->response = array(
            'type' => 'basic',
            'filename' => $filename,
            'videos_id' => $videos_id,
            'title' => strip_tags($video->getTitle()),
            'description' => strip_tags($video->getDescription()),
            'duration_in_seconds' => $video->getDuration_in_seconds(),
            'vtt' => $paths['url'],
            'text' => Ai_responses::getTranscriptionText($videos_id)
        );

        $obj->error = false;
        return $obj;
    }

    static function getVideoTranscriptionMetadata($videos_id){
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = '';
        $obj->response = array();

        if (empty($videos_id)) {
            $obj->msg = 'empty videos id';
            return $obj;
        }

        if(!Video::canEdit($videos_id)){
            $obj->msg = 'you cannot edit this video';
            return $obj;
        }

        $video = new Video('', '', $videos_id);
        $mp3 = false;
        $convert = self::getLowerMP3($videos_id);
        $fsize = 0;
        if(!empty($convert) && !empty($convert['url'])){
            $mp3 = $convert['url'];
            $fsize = filesize($convert['path']);
        }

        //var_dump($paths);exit;
        $obj->response = array(
            'type' => 'transcription',
            'videos_id' => $videos_id,
            'mp3' => $mp3,
            'filesize' => $fsize,
            'filesizeHuman' => humanFileSize($fsize),
            'duration_in_seconds' => $video->getDuration_in_seconds(),
        );

        $obj->error = false;
        return $obj;
    }

    static function getTokenForVideo($videos_id, $ai_responses_id, $param){
        global $global;
        $obj = new stdClass();
        $obj->videos_id = $videos_id;
        $obj->users_id = User::getId();
        $obj->ai_responses_id = $ai_responses_id;
        $obj->param = $param;
        $obj->created = time();

        return encryptString(_json_encode($obj));
    }

    
    static function getTokenFromRequest(){

        if(empty($_REQUEST['token'])){
            return false;
        }

        $string = decryptString($_REQUEST['token']);
        
        if(empty($string)){
            return false;
        }

        $json = _json_decode($string);

        if(empty($json)){
            return false;
        }
        
        return $json;
    }

    static function getLowerMP3($videos_id){
        $convert = convertVideoToMP3FileIfNotExists($videos_id);
        if(!empty($convert) && !empty($convert['url'])){
            $newPath = str_replace('.mp3', '_Low.mp3', $convert['path']);
            if(!file_exists($newPath)){
                $fromFileLocationEscaped = escapeshellarg($convert['path']);
                $toFileLocationEscaped = escapeshellarg($newPath);
                $command = get_ffmpeg()." -i {$fromFileLocationEscaped} -ar 16000 -ac 1 -b:a 16k {$toFileLocationEscaped}";
                $command =removeUserAgentIfNotURL($command);
                exec($command);
            }
            //var_dump($command, file_exists($newPath));exit;
            if(file_exists($newPath)){
                $convert['url'] = str_replace('.mp3', '_Low.mp3', $convert['url']);
                $convert['path'] = $newPath;
            }
        }
        return $convert;
    }
    
    
    public function getPluginMenu() {
        global $global;
        return '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/AI/View/editor.php\')" class="btn btn-primary btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';        
    }

    public function getVideosManagerListButton()
    {
        $obj = $this->getDataObject();
        $btn = '';
        $btn .= '<button type="button" ' .
            ' class="btn btn-default btn-light btn-sm btn-xs btn-block" ' .
            ' onclick="avideoModalIframe(webSiteRootURL+\\\'plugin/AI/videoAiSuggestions.php?videos_id=\'+row.id+\'\\\');" >' .
            ' <i class="fas fa-robot"></i> ' . __("AI-Powered SEO Insights") . '</button>';

        return $btn;
    }

    public static function getTagTypeId() {
        $VideoTags = AVideoPlugin::isEnabledByName('VideoTags');
        if(empty($VideoTags)){
            return false;
        }     
        $typeName = 'Keywords';
        $row = TagsTypes::getFromName($typeName);
        if(empty($row)){
            $tagType = new TagsTypes(0);
            $tagType->setName($typeName );
            return $tagType->save();
        }else{
            return $row['id'];
        }
    }


    static function deleteAllRecords()
    {
        $tables = array('ai_transcribe_responses', 'ai_metatags_responses', 'ai_responses');
        foreach ($tables as $key => $value) {
            $sql = "DELETE FROM {$value} ";
            $sql .= " WHERE id > 0 ";
            $global['lastQuery'] = $sql;
            sqlDAL::writeSql($sql);
        }
        
        return true;
    }

}
