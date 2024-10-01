<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class JustWatch extends PluginAbstract
{

  public function getTags()
  {
    return array(
      PluginTags::$FREE,
    );
  }

  public function getDescription()
  {
    global $global;
    $txt = "Provides a JustWatch feed at <a href='{$global['webSiteRootURL']}plugin/JustWatch/feed.json.php' target='_blank'>Feed.json</a>. 
        It aggregates links and package information for platforms integration with <a href='https://www.justwatch.com/' target='_blank'>JustWatch services</a>.";

    return $txt;
  }

  public function getName()
  {
    return "JustWatch";
  }

  public function getUUID()
  {
    return "JustWatch-43a9-479b-994a-5430dc22958c";
  }

  public static function getReleaseYear($videos_id)
  {
    $vt = AVideoPlugin::loadPluginIfEnabled('VideoTags');
    if (!empty($vt)) {
      $obj = AVideoPlugin::getDataObjectIfEnabled('JustWatch');
      $tags = VideoTags::getArrayFromVideosId($videos_id, $obj->release_year_tag_type_id);
      if (!empty($tags)) {
        return intval($tags[0]);
      }
    }
    return intval(date('Y'));
  }

  public static function getCrewMembers($videos_id)
  {
    $vt = AVideoPlugin::loadPluginIfEnabled('VideoTags');
    $crew = array();
    if (!empty($vt)) {
      $obj = AVideoPlugin::getDataObjectIfEnabled('JustWatch');
      $tags = VideoTags::getArrayFromVideosId($videos_id, $obj->crew_members_tag_type_id);
      if (!empty($tags)) {
        foreach ($tags as $key => $value) {
          $crew[] = [
            "role" => "actor",
            "full_name" => $value
          ];
        }
      }
    }
    return $crew;
  }

  public function getEmptyDataObject()
  {
    $obj = new stdClass();

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

    $obj->currency = 'USD';
    $obj->country_iso = 'US';
    $obj->release_year_tag_type_id = 8;
    $obj->crew_members_tag_type_id = 5;

    return $obj;
  }
}
