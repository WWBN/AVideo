<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class BitLy extends PluginAbstract {

    public function getDescription() {
        $desc = "Bit.ly Plugin";
        $desc .= ' A URL shortener built with powerful tools to help you grow and protect your brand.';
        $desc .= '<br>This plugin requires an <a href="https://app.bitly.com/settings/api/">Access token</a>';
        //$desc .= $this->isReadyLabel(array('YPTWallet'));
        return $desc;
    }

    public function getName() {
        return "BitLy";
    }

    public function getUUID() {
        return "BitLy-5ee8405eaaa16";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->AccessToken = "";
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
        return $obj;
    }

    static function getLink($videos_id) {
        if (empty($videos_id)) {
            return false;
        }

        $link = self::getBitLyLink($videos_id);
        
        if(empty($link)){
            $long_url = Video::getLinkToVideo($videos_id);
            $result = self::getAPIBitLyLink($long_url);
            if(!empty($result) && !empty($result->link)){
                self::setBitLyLink($videos_id, $result->link);
                return $result->link;
            }else{
                return false;
            }
        }
        return $link;
    }

    static function getAPIBitLyLink($long_url) {
        if (empty($long_url)) {
            return false;
        }

        $obj = AVideoPlugin::getDataObject('BitLy');
        
        $apiv4 = 'https://api-ssl.bitly.com/v4/bitlinks';

        $data = array(
            'long_url' => $long_url
        );
        $payload = json_encode($data);

        $header = array(
            'Authorization: Bearer ' . $obj->AccessToken,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        );

        $ch = curl_init($apiv4);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);

        return json_decode($result);
    }

    static function getBitLyLink($videos_id) {
        if (empty($videos_id)) {
            return false;
        }
        $video = new Video('', '', $videos_id);
        $externalOptions = _json_decode($video->getExternalOptions());
        if (empty($externalOptions->bitLyLink)) {
            return false;
        }
        return $externalOptions->bitLyLink;
    }

    static function setBitLyLink($videos_id, $link) {
        if (empty($videos_id)) {
            return false;
        }
        $video = new Video('', '', $videos_id);
        $externalOptions = _json_decode($video->getExternalOptions());
        $externalOptions->bitLyLink = $link;
        $video->setExternalOptions(json_encode($externalOptions));
        return $video->save();
    }

}
