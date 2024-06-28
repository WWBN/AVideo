<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class JustWatch extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE,
        );
    }

    public function getDescription() {
        $txt = "";

        return $txt;
    }

    public function getName() {
        return "JustWatch";
    }

    public function getUUID() {
        return "JustWatch-43a9-479b-994a-5430dc22958c";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->streaming_service_name = 'Example Streaming Service';
        $obj->streaming_service_url = 'https://www.example.com';

        $o = new stdClass();
        $o->type = "textarea";
        $o->value = '[
            {
              "platform": "apple",
              "url": "https://apps.apple.com/us/app/justwatch-movies-tv-shows/id979227482",
              "country_iso": "US"
            },
            {
              "platform": "google",
              "url": "https://play.google.com/store/apps/details?id=com.justwatch.justwatch\u0026hl=en\u0026gl=US",
              "country_iso": "XX"
            }
          ]';
        $obj->application_stores = $o;


        $o = new stdClass();
        $o->type = "textarea";
        $o->value = '[
            {
              "platform": "android_tv",
              "name": "com.justwatch.android-app",
              "country_iso": "XX"
            },
            {
              "platform": "ios_mobile",
              "name": "com.justwatch.ios-app",
              "country_iso": "XX"
            }
          ]';
        $obj->application_packages = $o;


        return $obj;
    }


}
