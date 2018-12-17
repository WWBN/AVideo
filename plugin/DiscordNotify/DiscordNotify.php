<?php

global $global;
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
		$obj->favicon_url = "";
		$obj->botuser = "";
		$obj->botlogo = "";
		$obj->baseurl = "";
        return $obj;
    }
    public function afterNewVideo($videos_id)
    {
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
		$favicon_url = $o->favicon_url;
		$botuser = $o->botuser;
		$baseurl = $o->baseurl;
		$botlogo = $o->botlogo;

$hookObject = json_encode([
    /*
     * The general "message" shown above your embeds
     */
    "content" => "",
    /*
     * The username shown in the message
     */
    "username" => $botuser,
    /*
     * The image location for the senders image
     */
    "avatar_url" => $favicon_url,
    /*
     * Whether or not to read the message in Text-to-speech
     */
    "tts" => false,
    /*
     * File contents to send to upload a file
     */
    // "file" => "",
    /*
     * An array of Embeds
     */
    "embeds" => [
        /*
         * Our first embed
         */
        [
            // Set the title for your embed
            "title" => $username . " just uploaded a video",

            // The type of your embed, will ALWAYS be "rich"
            "type" => "rich",

            // A description for your embed
            "description" => "",

            // The URL of where your title will be a link to
            "url" => $baseurl . "/" . $channelName,

            /* A timestamp to be displayed below the embed, IE for when an an article was posted
             * This must be formatted as ISO8601
             */
            "timestamp" => "",

            // The integer color to be used on the left side of the embed
            "color" => hexdec( "FF0000" ),

            // Footer object
            "footer" => [
                "text" => $botuser,
                "icon_url" => $botlogo
            ],

            "image" => [
                "url" => $videoThumbs,
            ],

            //"thumbnail" => [
             //   "url" => $userThumbnail
            //],

            // Field array of objects
            "fields" => [
                // Field 1
                [
                    "name" => "Video Name",
                    "value" => $videoName,
                    "inline" => true
                ],
                // Field 2
                [
                    "name" => "Video Link",
                    "value" => $videoLink,
                    "inline" => true
                ],
                // Field 3
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

