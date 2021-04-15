<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/YouTubeAPI/Objects/YouTubeUploads.php';

class YouTubeAPI extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE,
        );
    }
    public function getDescription() {
        $txt = "Upload your videos to YouTube using the YouTube API.<br>";
        //$txt .= "You can acquire an OAuth 2.0 <b>client ID</b> and <b>client secret</b> from the <a href='https://cloud.google.com/console'>Google Cloud Console</a>";
        $txt .= "<br>Set <b>developer key</b> to the API key value from the <b>Access tab</b> of the <a href='https://console.developers.google.com/'>Google API Console</a>
        <br>Please ensure that you have enabled the YouTube Data API for your project.";
        $help = "";
        //$help = "<br><small>Your files must be self-hosted and MP4 to be able to upload to YouTube (Does not work form HLS or Embed)</small>";
        return $txt . $help;
    }

    public function getName() {
        return "YouTubeAPI";
    }

    public function getUUID() {
        return "youtube225-3807-4167-ba81-0509dd280e06";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        //$obj->client_id = '';
        //$obj->client_secret = '';
        $obj->developer_key = '';
        $obj->developer_key1 = '';
        $obj->developer_key2 = '';
        $obj->developer_key3 = '';
        $obj->developer_key4 = '';
        $obj->developer_key5 = '';
        $obj->developer_key6 = '';
        $obj->developer_key7 = '';
        $obj->developer_key8 = '';
        $obj->developer_key9 = '';

        //$obj->automaticallyUploadToYouTube = false;
        $obj->keyword = '';
        $obj->regionCode = '';
        $obj->maxResults = 12;
        $obj->cacheTimeout = 3600;
        $obj->showGallerySection = true;
        $obj->gallerySectionTitle = "YouTube Videos"; //https://developers.google.com/youtube/v3/docs/search/list?hl=pt-br#exemplos

        return $obj;
    }

    public function afterNewVideo($videos_id) {
        $youTubeObj = $this->getDataObject();
        if ($youTubeObj->automaticallyUploadToYouTube) {
            //$this->upload($videos_id);
        }
    }

    public function getGallerySection() {
        global $global;
        $obj = $this->getDataObject();
        if (!empty($obj->showGallerySection)) {
            include $global['systemRootPath'] . 'plugin/YouTubeAPI/gallerySection.php';
        }
    }

    public function listVideos($try=0) {
        global $global;
        $youTubeObj = $this->getDataObject();
        if (empty($_GET['page'])) {
            $page = 1;
        } else {
            $page = intval($_GET['page']);
        }
        $name = "YouTubeAPI-ListVideos-{$page}-" . md5(@$_GET['search']);
        $cache = ObjectYPT::getCache($name, $youTubeObj->cacheTimeout);

        if (empty($cache)) {
            require_once $global['systemRootPath'] . 'plugin/YouTubeAPI/youtube-api/autoload.php';
            /*
             * Set $DEVELOPER_KEY to the "API key" value from the "Access" tab of the
             * Google API Console <https://console.developers.google.com/>
             * Please ensure that you have enabled the YouTube Data API for your project.
             */
            if(empty($try)){
                $DEVELOPER_KEY = $youTubeObj->developer_key;
            }else{
                $developer_key = "developer_key";
                eval("\$DEVELOPER_KEY = \$youTubeObj->{$developer_key}{$try};");
            }
            $object = new stdClass();
            $object->error = true;
            $object->msg = "";
            $object->videos = array();
            if(empty($DEVELOPER_KEY)){
                $object->msg = "The {$developer_key}{$try} is empty and we could not use";
                return $object;
            }

            _error_log("YouTubeAPI::listVideos try={$try} developer_key={$DEVELOPER_KEY}");

            try {

                $client = new Google_Client();
                $client->setDeveloperKey($DEVELOPER_KEY);

                // Define an object that will be used to make all API requests.
                $youtube = new Google_Service_YouTube($client);

                // Call the search.list method to retrieve results matching the specified
                // query term.

                $options = array(
                    //'q' => "best",
                    'videoEmbeddable' => 'true',
                    'videoSyndicated' => 'true',
                    'type' => 'video',
                    //'order' => 'viewCount',
                    'maxResults' => $youTubeObj->maxResults,
                    'part' => 'snippet',
                        //'chart' => 'mostPopular',
                        //'regionCode' => $youTubeObj->regionCode,
                );

                if (!empty($youTubeObj->keyword)) {
                    $options['q'] = $youTubeObj->keyword;
                }
                if (!empty($_GET['search'])) {
                    $options['q'] = $_GET['search'];
                }


                if (!empty($_GET['pageToken'])) {
                    $options['pageToken'] = $_GET['pageToken'];
                }
                $searchResponse = $youtube->search->listSearch('snippet,contentDetails,statistics', $options);
                // Add each result to the appropriate list, and then display the lists of
                // matching videos, channels, and playlists.
                $object->nextPageToken = @$searchResponse['nextPageToken'];
                $object->prevPageToken = @$searchResponse['prevPageToken'];

                foreach ($searchResponse['items'] as $searchResult) {
                    $vid = new YPTvideoObject(
                            $searchResult["id"]["videoId"], $searchResult['snippet']["title"], $searchResult['snippet']["description"], $searchResult['snippet']["thumbnails"]["high"]["url"], $searchResult['snippet']["channelTitle"], "https://www.youtube.com/embed/{$searchResult["id"]["videoId"]}");
                    $object->videos[] = $vid;
                }
                if (!empty($object->videos)) {
                    $object->error = false;
                    ObjectYPT::setCache($name, $object);
                } else {
                    $oldCache = ObjectYPT::getCache($name, 0);
                    if (!empty($oldCache->videos)) {
                        $cache = $oldCache;
                    }
                }
            } catch (Google_Service_Exception $e) {
                $object->msg = _json_decode($e->getMessage());
            } catch (Google_Exception $e) {
                $object->msg = _json_decode($e->getMessage());
            }
            if($try<10){
                _error_log("YouTubeAPI Error: ".json_encode($object));
                return $this->listVideos($try+1);
            }else{
                return $object;
            }
        } else {
            $cache->error = false;
            return $cache;
        }
    }

    /* Not ready yet
      public function upload($videos_id) {
      global $global;
      require_once $global['systemRootPath'] . 'plugin/YouTubeAPI/youtube-api/autoload.php';
      $object = new stdClass();
      $object->error = true;
      $object->msg = "";
      $object->url = self::getUploadedURL($videos_id);
      $object->databaseSaved = false;

      _error_log('YouTube::upload start ' . $videos_id);
      if (!empty($object->url)) {
      $object->msg = __("Video already uploaded") . " " . $object->url;
      $object->databaseSaved = true;
      _error_log('YouTube::upload ' . $object->msg);
      return $object;
      }

      $v = new Video("", "", $videos_id);

      if (empty($v->getFilename())) {
      $object->msg = __("Video Filename not found");
      _error_log('YouTube::upload ' . $object->msg);
      return $object;
      }

      $source = Video::getHigestResolutionVideoMP4Source($v->getFilename());
      $file_name = $source['path'];

      if (!file_exists($file_name)) {
      $object->msg = __("Video MP4 File was not found");
      _error_log('YouTube::upload ' . $object->msg . " $file_name");
      return $object;
      }

      $youTubeObj = $this->getDataObject();

      $client = new Google_Client();
      $client->setClientId($youTubeObj->client_id);
      $client->setClientSecret($youTubeObj->client_secret);
      $client->setScopes('https://www.googleapis.com/auth/youtube');
      //$redirectUri = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],FILTER_SANITIZE_URL);
      $redirectUri = "{$global['webSiteRootURL']}plugin/YouTubeAPI/redirect.json.php";
      $client->setRedirectUri($redirectUri);
      // Define an object that will be used to make all API requests.
      $youtube = new Google_Service_YouTube($client);
      // Check if an auth token exists for the required scopes
      $tokenSessionKey = 'token-' . $client->prepareScopes();


      if (isset($_SESSION[$tokenSessionKey])) {
      $client->setAccessToken($_SESSION[$tokenSessionKey]);
      }
      // Check to ensure that the access token was successfully acquired.
      if ($client->getAccessToken()) {
      try {

      $time_start = microtime(true);
      // REPLACE this value with the path to the file you are uploading.
      $videoPath = $v->getExistingVideoFile();
      // Create a snippet with title, description, tags and category ID
      // Create an asset resource and set its snippet metadata and type.
      // This example sets the video's title, description, keyword tags, and
      // video category.
      $snippet = new Google_Service_YouTube_VideoSnippet();
      $snippet->setTitle($v->getTitle());
      $snippet->setDescription($v->getDescription());
      $snippet->setTags(array("AVideo", $config->getWebSiteTitle()));
      // Numeric video category. See
      // https://developers.google.com/youtube/v3/docs/videoCategories/list
      // $snippet->setCategoryId("22");
      // Set the video's status to "public". Valid statuses are "public",
      // "private" and "unlisted".
      $status = new Google_Service_YouTube_VideoStatus();
      $privacy_view = "public";
      if (!empty($video->getVideo_password())) {
      $privacy_view = "private";
      } else if ($video->getType() == 'u') {
      $privacy_view = "unlisted";
      } else if (!Video::isPublic($videos_id)) {
      $privacy_view = "private";
      }
      $status->privacyStatus = $privacy_view;
      // Associate the snippet and status objects with a new video resource.
      $video = new Google_Service_YouTube_Video();
      $video->setSnippet($snippet);
      $video->setStatus($status);
      // Specify the size of each chunk of data, in bytes. Set a higher value for
      // reliable connection as fewer chunks lead to faster uploads. Set a lower
      // value for better recovery on less reliable connections.
      $chunkSizeBytes = 1 * 1024 * 1024;
      // Setting the defer flag to true tells the client to return a request which can be called
      // with ->execute(); instead of making the API call immediately.
      $client->setDefer(true);
      // Create a request for the API's videos.insert method to create and upload the video.
      $insertRequest = $youtube->videos->insert("status,snippet", $video);
      // Create a MediaFileUpload object for resumable uploads.
      $media = new Google_Http_MediaFileUpload(
      $client, $insertRequest, 'video/*', null, true, $chunkSizeBytes
      );
      $media->setFileSize(filesize($videoPath));
      // Read the media file and upload it chunk by chunk.
      $status = false;
      $handle = fopen($videoPath, "rb");
      while (!$status && !feof($handle)) {
      $chunk = fread($handle, $chunkSizeBytes);
      $status = $media->nextChunk($chunk);
      }
      fclose($handle);
      // If you want to make other calls after the file upload, set setDefer back to false
      $client->setDefer(false);

      $time_end = microtime(true);
      $time = $time_end - $time_start; //Time it took
      $bytes_per_sec = $bytes / $time;
      $KB_per_sec = $bytes_per_sec / 1024;
      $MB_per_sec = intval($KB_per_sec / 1024);

      $object->error = false;
      $object->url = 'https://youtu.be/' . $status['id'];
      $object->msg = $file_name . ' has been uploaded to ' . $object->url . " took " . secondsToHumanTiming($time) . " to complete";
      _error_log('YouTube::upload ' . $object->msg . " {$MB_per_sec} Mbps");

      $saveUpload = new YouTubeUploads(0);
      $saveUpload->setVideos_id($videos_id);
      $saveUpload->setUrl($object->url);
      $object->databaseSaved = $saveUpload->save();

      $v->setYoutubeId($status['id']);
      $v->save();
      } catch (Google_Service_Exception $e) {
      $object->msg = sprintf(__("A service error occurred: %s"), $e->getMessage());
      } catch (Google_Exception $e) {
      $object->msg = sprintf(__("An client error occurred: %s"), $e->getMessage());
      }
      $_SESSION[$tokenSessionKey] = $client->getAccessToken();
      } elseif (empty($youTubeObj->client_id) || empty($youTubeObj->client_secret)) {
      $object->msg = "<h3>Client Credentials Required</h3>
      <p>
      You need to set <code>\$OAUTH2_CLIENT_ID</code> and
      <code>\$OAUTH2_CLIENT_ID</code> before proceeding.
      <p>";
      } else {
      // If the user hasn't authorized the app, initiate the OAuth flow
      $state = mt_rand();
      $client->setState($state);
      $_SESSION['state'] = $state;
      $authUrl = $client->createAuthUrl();
      $object->msg = "<h3>Authorization Required</h3><p>You need to <a href=\"{$authUrl}\"  class='btn btn-danger'><span class='fab fa-youtube-square'></span> authorize access</a> before proceeding.<p>";
      }

      return $object;
      }

      public function getHeadCode() {
      global $global;
      $baseName = basename($_SERVER['REQUEST_URI']);
      $js = "";
      if ($baseName === 'mvideos') {
      $js .= "<script>function youTubeUpload(video_id){
      modal.showPleaseWait();
      \$.ajax({
      url: '{$global['webSiteRootURL']}plugin/YouTubeAPI/upload.json.php',
      data: {\"video_id\": video_id},
      type: 'post',
      success: function (response) {
      if(response.error){
      avideoAlert('" . __("Sorry!") . "', response.msg, 'error');
      }else{
      avideoAlert('" . __("Congratulations!") . "', response.msg, 'success');
      }
      console.log(response);
      modal.hidePleaseWait();
      }
      });}</script>";
      } else
      if ($baseName === 'plugins') {
      $js .= "<script>function youTubeUploadAll(){
      \$.ajax({
      url: '{$global['webSiteRootURL']}plugin/YouTubeAPI/uploadAll.json.php',
      success: function (response) {
      if(response.error){
      avideoAlert('" . __("Sorry!") . "', response.msg, 'error');
      }else{
      avideoAlert('" . __("Congratulations!") . "', response.msg, 'success');
      }
      console.log(response);
      }
      });
      avideoAlert('" . __("Process Start") . "', 'It may take a while', 'warning');
      }</script>";
      }
      return $js;
      }

      public function getVideosManagerListButton() {
      $btn = '<button type="button" class="btn btn-danger btn-sm btn-xs btn-block " onclick="youTubeUpload(\' + row.id + \');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="Upload to YouTube"><i class="fab fa-youtube"></i> Upload</button>';
      return $btn;
      }

      public static function getUploadedURL($videos_id) {
      $youTubeUpload = new YouTubeUploads(0);
      $youTubeUpload->loadFromVideosID($videos_id);
      if (empty($youTubeUpload->getUrl())) {
      return false;
      }
      return $youTubeUpload->getUrl();
      }

      public function getPluginMenu() {
      global $global;
      $link = "<button class='btn btn-primary btn-xs btn-block' title='Upload All Videos to YouTube' onclick='youTubeUploadAll()'><i class='fab fa-youTube-v'></i> Upload All Videos</button>";
      return $link;
      }
     *
     */
}
