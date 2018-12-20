<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class SlackNotify extends PluginAbstract
{
    
    public function getDescription()
    {
        return "Send video upload notifications to Slack webhook";
    }
    
    public function getName()
    {
        return "SlackNotify";
    }
    
    public function getUUID()
    {
        return "cf145581-7d5e-4bb6-8c13-848a19j1564h";
    }
    public function getTags()
    {
        return array(
            'free',
            'notifications',
            'webhook'
        );
    }
    public function getPluginVersion()
    {
        return "1.0";
    }
    public function getEmptyDataObject()
    {
        global $global;
        $server = parse_url($global['webSiteRootURL']);
        
        $obj              = new stdClass();
        $obj->webhook_url = "";
        return $obj;
    }
    public function afterNewVideo($videos_id)
    {
        global $global;
        $o                = $this->getDataObject();
        $users_id         = Video::getOwner($videos_id);
        $user             = new User($users_id);
        $username         = $user->getNameIdentification();
        $channelName      = $user->getChannelName();
        $video            = new Video("", "", $videos_id);
        $videoName        = $video->getTitle();
        $images           = Video::getImageFromFilename($video->getFilename());
        $videoThumbs      = $images->thumbsJpg;
        $videoLink        = Video::getPermaLink($videos_id);
        $videoDuration    = $video->getDuration();
        $videoDescription = $video->getDescription();
        $url              = $o->webhook_url;
        $message          = array(
            'payload' => json_encode(array(
                'text' => $username . " just uploaded a video\nVideo Name: " . $videoName . "\nVideo Link: " . $videoLink . "\nVideo Duration: " . $videoDuration
            ))
        );
        $c                = curl_init($url);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $message);
        curl_exec($c);
        curl_close($c);
    }
}
