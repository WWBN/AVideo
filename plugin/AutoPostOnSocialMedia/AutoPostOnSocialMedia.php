<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

class AutoPostOnSocialMedia extends PluginAbstract {
    
    static $scheduleType = 'AutoPostOnSocialMedia';
    
    public function getDescription() {
        $desc = "AutoPostOnSocialMedia Plugin
            
    Our final goal is to post a tweet on your Twitter account using the REST API. This process requires you to register the application on Twitter and get the API keys.

These API keys are act like your identity for your Twitter account. To create application, go to <a href='https://apps.twitter.com/' target='_blank'>Twitter Apps</a> and follow the below steps.

Click the button \"Create New App\".
Fill up Name, Description, Website fields.
Accept agreement and click the button \"Create your Twitter application\".
On the next page, click on the tab \"Keys and Access Tokens\". Under this tab you will find your Consumer Key and Consumer Secret. Copy these details and store it in safe place.
Under the same tab, you will see the section \"Your Access Token\". Click on the button \"Create Access Token\".
At this step, copy your Access Token and Access Token Secret. Keep these details safe.

Login in your app: http://dev.twitter.com/apps
In the Settings tab, change the Application type to Read, Write and Access direct messages
In the Reset keys tab, press the Reset button, update the consumer key and secret in your application accordingly.";

        $desc = nl2br($desc);
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

        return $obj;
    }

    public function getPluginMenu() {
        global $global;
        $btn = '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/AutoPostOnSocialMedia/View/editor.php\')" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';
        $btn .= '<button onclick="avideoAjax(webSiteRootURL+\'plugin/AutoPostOnSocialMedia/autopost.json.php\', {})" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fab fa-twitter"></i> Autopost now</button>';
        return $btn;
    }

    static function postVideo($videos_id) {
        $url = Video::getLinkToVideo($videos_id, "", false, "permalink", $get);
        return self::post($url);
    }

    static function post($msg) {
        $obj = AVideoPlugin::getDataObject('AutoPostOnSocialMedia');
        //var_dump($obj->TwitterAPIKey, $obj->TwitterAPIKeySecret, $obj->TwitterAccessToken, $obj->TwitterAccessTokenSecret, $msg);exit;
        
        if($obj->TwitterEnable){
            $connection = new TwitterOAuth($obj->TwitterAPIKey, $obj->TwitterAPIKeySecret, $obj->TwitterAccessToken, $obj->TwitterAccessTokenSecret);
            $post_tweets = $connection->post("statuses/update", ["status" => $msg]);
        }

        return $post_tweets;
    }

    public function getVideosManagerListButton() {
        if (!User::isAdmin()) {
            return "";
        }
        $btn = '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block " onclick="avideoAlertAJAX(webSiteRootURL+\\\'plugin/AutoPostOnSocialMedia/post.json.php?videos_id=\' + row.id + \'\\\');" ><i class="fab fa-twitter"></i> Post On Twitter</button>';
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
