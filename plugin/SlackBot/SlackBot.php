<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';

class SlackBot extends PluginAbstract
{

    public function getDescription()
    {
        return "Send video upload notifications to Users on Slack who have subscribed to the channel via a Slack Bot.
        <br><strong>The following scopes are required:</strong>
            <br>-chat:write:bot
            <br>-bot
            <br>-users:read
            <br>-users:read:email";
    }

    public function getName()
    {
        return "SlackBot";
    }

    public function getUUID()
    {
        return "cf145581-7d5e-4bb6-8c13-848a19j1564a";
    }
    public function getTags()
    {
        return array(
            'free',
            'notifications',
            'bot'
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
        $obj->bot_user_oauth_access_token = "";
        return $obj;
    }
    public function afterNewVideo($videos_id)
    {
        global $global;
        $o                = $this->getDataObject();
        $users_id         = Video::getOwner($videos_id);
        $user             = new User($users_id);
        $usersSubscribed  = Subscribe::getAllSubscribes($users_id);
        $username         = $user->getNameIdentification();
        $channelName      = $user->getChannelName();
        $video            = new Video("", "", $videos_id);
        $videoName        = $video->getTitle();
        $images           = Video::getImageFromFilename($video->getFilename());
        $videoThumbs      = $images->thumbsJpg;
        $videoLink        = Video::getPermaLink($videos_id);
        $videoDuration    = $video->getDuration();
        $videoDescription = $video->getDescription();
        $token            = $o->bot_user_oauth_access_token;

        //For each user email, get the slack id, and post a message to the slack id
        foreach ($usersSubscribed as $subscribedUser) {
            if ($subscribedUser["status"] == "a" && $subscribedUser["notify"] == true) {
                //Get the users slack id
                $headers = array(
                    'Content-type: application/json',
                    'Accept-Charset: UTF-8',
                    'Authorization: Bearer ' . $token,
                );
                $c            = curl_init('https://slack.com/api/users.lookupByEmail?email=' . $subscribedUser["email"]);
                curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($c);
                $userSlackInformation = json_decode($result);
                if ($userSlackInformation->ok == true) {
                    $slackChannel = $userSlackInformation->user->id;
                } else {
                    $slackChannel = "";
                    error_log("Slack id for user email: " . $subscribedUser["email"] . " could not be found");
                }
                curl_close($c);

                if ($slackChannel != "") {
                    //Send the message to the user as a slack bot if the slack channel was returned for the users email
                    $paylod->text     = $username . " just uploaded a video\nVideo Name: " . $videoName . "\nVideo Link: " . $videoLink . "\nVideo Duration: " . $videoDuration;
                    $paylod->channel  = $slackChannel;
                    $message          = json_encode($paylod);
                    $headers = array(
                        'Content-type: application/json',
                        'Accept-Charset: UTF-8',
                        'Authorization: Bearer ' . $token,
                    );
                    $cBot         = curl_init('https://slack.com/api/chat.postMessage');
                    curl_setopt($cBot, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($cBot, CURLOPT_POST, true);
                    curl_setopt($cBot, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($cBot, CURLOPT_POSTFIELDS, $message);
                    curl_exec($cBot);
                    curl_close($cBot);
                }
            }
        }


    }
}