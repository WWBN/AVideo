<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

class AutoPostOnSocialMedia extends PluginAbstract {
    
    static $scheduleType = 'AutoPostOnSocialMedia';
    
    public function getDescription() {
        $desc = "Helps you automatically post your content on multiple social media platforms (Cuttently Twitter only)";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/AutoPostOnSocialMedia-Plugin' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";

        $desc = $desc.$help ;
        return $desc;
    }

    public function getName() {
        return "AutoPostOnSocialMedia";
    }

    public function getUUID() {
        return "AutoPostOnSocialMedia-5ee8405eaaa16";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function updateScript() {
        global $global;
        /*
          if (AVideoPlugin::compareVersion($this->getName(), "2.0") < 0) {
          sqlDal::executeFile($global['systemRootPath'] . 'plugin/PayPerView/install/updateV2.0.sql');
          }
         * 
         */
        return true;
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
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
        $obj->TwitterEnable = true;
        $obj->TwitterAPIKey = "";
        $obj->TwitterAPIKeySecret = "";
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->TwitterBearerToken = $o;
        $obj->TwitterAccessToken = "";
        $obj->TwitterAccessTokenSecret = "";

        $o = new stdClass();
        $o->type = [1 => 'API Version 1', '1.1' => 'API Version 1.1', 2=>'API Version 2'];
        $o->value = '1.1';
        $obj->apiVersion = $o;

        $o = new stdClass();
        $o->type = array(
            0 => __("All time"),
            1 => __("1 Day"),
            7 => __("7 Day"),
            30 => __("1 Month"),
            60 => __("2 Months"),
            90 => __("3 Months"),
            182 => __("6 Months"),
            365 => __("12 Months"));
        $o->value = 365;
        $obj->postARandomVideoFromLastDays = $o;
        
        $obj->debugMode = true;

        return $obj;
    }

    public function getPluginMenu() {
        global $global;
        $btn = '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/AutoPostOnSocialMedia/View/editor.php\')" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';
        $btn .= '<button onclick="avideoAjax(webSiteRootURL+\'plugin/AutoPostOnSocialMedia/autopost.json.php\', {})" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa-brands fa-x-twitter"></i> Autopost now</button>';
        return $btn;
    }

    static function postVideo($videos_id) {
        if(AVideoPlugin::isEnabledByName('BitLy')){
            $url = BitLy::getLink($videos_id);
        }else{
            $url = Video::getLinkToVideo($videos_id, "", false, Video::$urlTypeShort);
        }
        _error_log("AutoPostOnSocialMedia::postVideo($videos_id) $url");
        return self::post($url);
    }

    static function post($msg) {
        $obj = AVideoPlugin::getDataObject('AutoPostOnSocialMedia');
        //var_dump($obj->TwitterAPIKey, $obj->TwitterAPIKeySecret, $obj->TwitterAccessToken, $obj->TwitterAccessTokenSecret, $msg);exit;
        
        if($obj->debugMode){
            _error_log('AutoPostOnSocialMedia start');
        }
        if($obj->TwitterEnable){
            $connection = new TwitterOAuth($obj->TwitterAPIKey, $obj->TwitterAPIKeySecret, $obj->TwitterAccessToken, $obj->TwitterAccessTokenSecret);
            if($obj->apiVersion->value>1){
                _error_log('AutoPostOnSocialMedia apiVersion '.$obj->apiVersion->value);
                $connection->setApiVersion("{$obj->apiVersion->value}");
            }
            if($obj->debugMode){
                _error_log($msg);
            }
            $post_tweets = $connection->post("statuses/update", ["status" => $msg]);
            
            if($obj->debugMode){
                _error_log("getLastHttpCode: ". $connection->getLastHttpCode());
                //_error_log("getLastBody: ". $connection->getLastBody());
                //_error_log("getLastXHeaders: ". $connection->getLastXHeaders());
                //_error_log("getLastApiPath: ". $connection->getLastApiPath());
                _error_log(json_encode($post_tweets), AVideoLog::$DEBUG);
            }
        }else{
            if($obj->debugMode){
                _error_log('Tweeter disabled');
            }
        }

        return $post_tweets;
    }

    public function getVideosManagerListButton() {
        if (!User::isAdmin()) {
            return "";
        }
        $btn = '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block " onclick="avideoAlertAJAX(webSiteRootURL+\\\'plugin/AutoPostOnSocialMedia/post.json.php?videos_id=\' + row.id + \'\\\');" ><i class="fa-brands fa-x-twitter"></i> Post On Twitter</button>';
        return $btn;
    }

    static function getRandomVideo() {
        $obj = AVideoPlugin::getDataObject('AutoPostOnSocialMedia');
        $days = $obj->postARandomVideoFromLastDays->value;

        $sql = "SELECT * FROM videos WHERE 1=1 ";
        $sql .= " AND status IN ('" . implode("','", Video::getViewableStatus(false)) . "')";
        if(!empty($days)){
            $sql .=" AND created >= ( CURDATE() - INTERVAL {$days} DAY ) ";
        }
        $sql .= " ORDER BY RAND() LIMIT 1 ";
        //echo var_dump($days, $sql);
        $res = sqlDAL::readSql($sql, "", array(), true);
        $video = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return $video;
    }

}
