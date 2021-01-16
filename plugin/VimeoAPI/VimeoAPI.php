<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/VimeoAPI/Objects/VimeoUploads.php';
require_once $global['systemRootPath'] . 'plugin/VimeoAPI/vimeo-api/autoload.php';

use Vimeo\Vimeo;
use Vimeo\Exceptions\VimeoUploadException;

class VimeoAPI extends PluginAbstract {

    public function getTags() {
        return array(
        );
    }
    public function getDescription() {
        $txt = "Upload your videos to Vimeo using the Vimeo API.<br>";
        $txt .= "<a href='https://developer.vimeo.com/apps/'>Create an APP and get your credentials here</a><br>";
        $txt .= "You MUST generate a token Authenticated + Private Scope + Upload <br>";
        $help = "<small>Your files must be self-hosted and MP4 to be able to upload to Vimeo (Does not work form HLS or Embed)</small>";
        return $txt . $help;
    }

    public function getName() {
        return "VimeoAPI";
    }

    public function getUUID() {
        return "vimeo225-3807-4167-ba81-0509dd280e06";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->client_id = '';
        $obj->client_secret = '';
        $obj->access_token = '';

        $obj->automaticallyUploadToVimeo = false;

        return $obj;
    }

    public function afterNewVideo($videos_id) {
        $vimeoObj = $this->getDataObject();
        if ($obj->automaticallyUploadToVimeo) {
            $this->upload($videos_id);
        }
    }

    public function upload($videos_id) {
        $object = new stdClass();
        $object->error = true;
        $object->msg = "";
        $object->url = self::getUploadedURL($videos_id);
        $object->databaseSaved = false;

        _error_log('Vimeo::upload start ' . $videos_id);
        if (!empty($object->url)) {
            $object->msg = __("Video already uploaded") . " " . $object->url;
            $object->databaseSaved = true;
            _error_log('Vimeo::upload ' . $object->msg);
            return $object;
        }

        $video = new Video("", "", $videos_id);

        if (empty($video->getFilename())) {
            $object->msg = __("Video Filename not found");
            _error_log('Vimeo::upload ' . $object->msg);
            return $object;
        }

        $source = Video::getHigestResolutionVideoMP4Source($video->getFilename());
        $file_name = $source['path'];
        
        if(!file_exists($file_name)){
            $object->msg = __("Video MP4 File was not found");
            _error_log('Vimeo::upload ' . $object->msg." $file_name");
            return $object;
        }
        
        $vimeoObj = $this->getDataObject();
        if (empty($vimeoObj->access_token)) {
            $object->msg = 'You can not upload a file without an access token. You can find this token on your app page';
            _error_log('Vimeo::upload ' . $object->msg);
            return $object;
        }

        // Instantiate the library with your client id, secret and access token (pulled from dev site)
        $lib = new Vimeo($vimeoObj->client_id, $vimeoObj->client_secret, $vimeoObj->access_token);


        $bytes = filesize($file_name);
        _error_log('Vimeo::upload Uploading... ' . $file_name . ' ' . humanFileSize($bytes));

        try {
            $privacy_view = "anybody";
            if (!empty($video->getVideo_password())) {
                $privacy_view = "password";
            } else if ($video->getType() == 'u') {
                $privacy_view = "unlisted";
            } else if (!Video::isPublic($videos_id)) {
                $privacy_view = "nobody";
            }

            $params = array(
                'name' => $video->getTitle(),
                'description' => $video->getDescription(),
                'privacy.view' => $privacy_view);

            if ($privacy_view == "password") {
                $params["password"] = $video->getVideo_password();
            }
            $time_start = microtime(true);
            // Upload the file and include the video title and description.
            $uri = $lib->upload($file_name, $params);

            // Get the metadata response from the upload and log out the Vimeo.com url
            $video_data = $lib->request($uri . '?fields=link');
            $time_end = microtime(true);
            $time = $time_end - $time_start; //Time it took
            $bytes_per_sec = $bytes / $time;
            $KB_per_sec = $bytes_per_sec / 1024;
            $MB_per_sec = intval($KB_per_sec / 1024);


            $object->error = false;
            $object->msg = $file_name . ' has been uploaded to ' . $video_data['body']['link'] . " took ".  secondsToHumanTiming($time)." to complete";
            $object->url = $video_data['body']['link'];
            _error_log('Vimeo::upload ' . $object->msg . " {$MB_per_sec} Mbps");

            $saveUpload = new VimeoUploads(0);
            $saveUpload->setVideos_id($videos_id);
            $saveUpload->setUrl($object->url);
            $object->databaseSaved = $saveUpload->save();


            // Make an API call to see if the video is finished transcoding.
            //$video_data = $lib->request($uri . '?fields=transcode.status');
            //echo 'The transcode status for ' . $uri . ' is: ' . $video_data['body']['transcode']['status'] . "\n";
        } catch (VimeoUploadException $e) {
            // We may have had an error. We can't resolve it here necessarily, so report it to the user.
            $object->msg = 'Error uploading (' . $video->getTitle() . ') ' . $e->getMessage();
            _error_log('Vimeo::upload ' . $file_name . ' ' . $object->msg);
        } catch (VimeoRequestException $e) {
            $object->msg = 'Error uploading (' . $video->getTitle() . ') ' . $e->getMessage();
            _error_log('Vimeo::upload ' . $file_name . ' ' . $object->msg);
        }
        return $object;
    }

    public function getHeadCode() {
        global $global;
        $baseName = basename($_SERVER['REQUEST_URI']);
        $js = "";
        if ($baseName === 'mvideos') {
            $js .= "<script>function vimeoUpload(video_id){
                                    modal.showPleaseWait();
                                    \$.ajax({
                                        url: '{$global['webSiteRootURL']}plugin/VimeoAPI/upload.json.php',
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
            $js .= "<script>function vimeoUploadAll(){
                                    \$.ajax({
                                        url: '{$global['webSiteRootURL']}plugin/VimeoAPI/uploadAll.json.php',
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
        $btn = '<button type="button" class="btn btn-default btn-light btn-sm btn-xs  btn-block" onclick="vimeoUpload(\' + row.id + \');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="Upload to Vimeo"><i class="fab fa-vimeo-v"></i> Upload</button>';
        return $btn;
    }

    public static function getUploadedURL($videos_id) {
        $vimeoUpload = new VimeoUploads(0);
        $vimeoUpload->loadFromVideosID($videos_id);
        if (empty($vimeoUpload->getUrl())) {
            return false;
        }
        return $vimeoUpload->getUrl();
    }

    public function getPluginMenu() {
        global $global;
        $link = "<button class='btn btn-primary btn-xs btn-block' title='Upload All Videos to Vimeo' onclick='vimeoUploadAll()'><i class='fab fa-vimeo-v'></i> Upload All Videos</button>";
        return $link;
    }

}
