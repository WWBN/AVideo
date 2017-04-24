<?php

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';

function getRealIpAddr() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

class VideoStatistic {

    private $id;
    private $when;
    private $ip;
    private $users_id;
    private $videos_id;

    static function save($videos_id) {
        global $global;

        if (empty($videos_id)) {
            die(__("You need a video to generate statistics"));
        }

        $userId = empty($_SESSION["user"]["id"]) ? "NULL" : $_SESSION["user"]["id"];

        $sql = "INSERT INTO videos_statistics "
                . "(when,ip, users_id, videos_id) values "
                . "(now(),'".getRealIpAddr()."',{$userId}, '{$videos_id}')";
        $insert_row = $global['mysqli']->query($sql);

        if ($insert_row) {
            if (empty($this->id)) {
                return $global['mysqli']->insert_id;
            } else {
                return $this->id;
            }
        } else {
            die($sql . ' Save Video Statistics Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }


}
