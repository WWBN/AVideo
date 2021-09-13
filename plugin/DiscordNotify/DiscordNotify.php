<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class DiscordNotify extends PluginAbstract {


    public function getTags() {
        return array(
            PluginTags::$FREE
        );
    }
    public function getDescription() {
        return "Send video upload notifications to discord webhook";
    }

    public function getName() {
        return "DiscordNotify";
    }

    public function getUUID() {
        return "cf145581-7d5e-4bb6-8c12-848a19j1564g";
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

    public function afterNewVideo($videos_id) {
		_error_log("DiscordNotify:afterNewVideo start");
        global $global;
        $o = $this->getDataObject();
        $users_id = Video::getOwner($videos_id);
        $user = new User($users_id);
        $username = $user->getNameIdentification();
        $channelName = $user->getChannelName();
        $video = new Video("", "", $videos_id);
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
		_error_log("DiscordNotify:afterNewVideo: {$url}");

         $hookObject = json_encode([
            "content" => "",
            "username" => $bot_username,
            "avatar_url" => $avatar_url,
            "tts" => false,
            "embeds" => [
                [
                    "title" => $username . " just uploaded a video",
                    "type" => "rich",
                    "url" => $global['webSiteRootURL'] . $channelName,
                    "timestamp" => gmdate('Y-m-d\TH:i:s', time()),
                    "color" => hexdec("FF0000"),
                    "footer" => [
                        "text" => $bot_username,
                        "icon_url" => $footer_image
                    ],
                    "image" => [
                        "url" => $videoThumbs,
                    ],
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
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $c = curl_init($url);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, $hookObject);
		curl_setopt($c, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
		));
		curl_exec($c);
		curl_close($c);
    }
}
