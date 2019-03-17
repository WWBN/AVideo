<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class DiscordNotify extends PluginAbstract
{
    
    public function getDescription()
    {
        return "Send video upload notifications to discord webhook";
    }
    
    public function getName()
    {
        return "DiscordNotify";
    }
    
    public function getUUID()
    {
        return "cf145581-7d5e-4bb6-8c12-848a19j1564g";
    }
    public function getTags()
    {
        return array(
            'free',
            'notifications',
            'webhook'
        );
    }
	public function getPluginVersion() {
        return "1.0";
    }
    public function getEmptyDataObject() {
        global $global;
        $server = parse_url($global['webSiteRootURL']);     
        
        $obj = new stdClass();
        $obj->webhook_url = "";
		$obj->avatar_url = "";
		$obj->bot_username = "";
		$obj->footer_image = "";
        return $obj;
    }
    public function afterNewVideo($videos_id)
    {
		global $global;
        $o = $this->getDataObject();
		$users_id = Video::getOwner($videos_id);
		$user = new User($users_id);
		$username = $user->getNameIdentification();
		$channelName = $user->getChannelName();
		$video = new Video("","",$videos_id);
		$videoName = $video->getTitle();
		$images = Video::getImageFromFilename($video->getFilename());
		$videoThumbs = $images->thumbsJpg;
                $videoLink = Video::getPermaLink($videos_id);
		$videoDuration = $video->getDuration();
		$videoDescription = $video->getDescription();
		$url = $o->webhook_url;
		$avatar_url = $o->avatar_url;
		$bot_username = $o->bot_username;
		$footer_image = $o->footer_image;

$hookObject = json_encode([
    "content" => "",
    "username" => $bot_username,
    "avatar_url" => $avatar_url,
    "tts" => false,
    "embeds" => [
        [
            "title" => $username . " just uploaded a video",
            "type" => "rich",
            "description" => "",
            "url" => $global['webSiteRootURL'] . $channelName,
            "timestamp" => "",
            "color" => hexdec( "FF0000" ),
            "footer" => [
                "text" => $bot_username,
                "icon_url" => $footer_image
            ],

            "image" => [
                "url" => $videoThumbs,
            ],

            //"thumbnail" => [
             //   "url" => $userThumbnail
            //],

            "fields" => [
                [
                    "name" => "Video Name",
                    "value" => $videoName,
                    "inline" => true
                ],
                [
                    "name" => "Video Link",
                    "value" => $videoLink,
                    "inline" => true
                ],
                [
                    "name" => "Video Duration",
                    "value" => $videoDuration,
                    "inline" => true
                ],
				[
                    "name" => "Video Description",
                    "value" => "N/A",
                    "inline" => true
                ]
            ]
        ]
    ]

], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

$ch = curl_init();

curl_setopt_array( $ch, [
    CURLOPT_URL => $url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $hookObject,
    CURLOPT_HTTPHEADER => [
        "Length" => strlen( $hookObject ),
        "Content-Type" => "application/json"
    ]
]);

return curl_exec( $ch );
    }
}
