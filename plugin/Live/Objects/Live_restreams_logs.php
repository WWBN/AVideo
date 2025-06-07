<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Live_restreams_logs extends ObjectYPT
{

    protected $id, $restreamer, $m3u8, $logFile, $json, $live_transmitions_history_id, $live_restreams_id;

    static function getSearchFieldsNames()
    {
        return array('restreamer', 'm3u8', 'logFile', 'json');
    }

    static function getTableName()
    {
        return 'live_restreams_logs';
    }

    function setId($id)
    {
        $this->id = intval($id);
    }

    function setRestreamer($restreamer)
    {
        if (!isValidURL($restreamer)) {
            return false;
        }
        $parts = explode('?', $restreamer);
        $this->restreamer = $parts[0];
    }

    function setM3u8($m3u8)
    {
        if (!isValidURL($m3u8)) {
            return false;
        }
        $this->m3u8 = $m3u8;
    }

    function setLogFile($logFile)
    {
        $logFile = basename($logFile);
        $this->logFile = $logFile;
    }

    function setJson($json)
    {
        $this->json = $json;
    }

    function setLive_transmitions_history_id($live_transmitions_history_id)
    {
        $this->live_transmitions_history_id = intval($live_transmitions_history_id);
    }

    function setLive_restreams_id($live_restreams_id)
    {
        $this->live_restreams_id = intval($live_restreams_id);
    }


    function getId()
    {
        return intval($this->id);
    }

    /**
     * @return string
     */
    function getRestreamer()
    {
        return $this->restreamer;
    }

    function getM3u8()
    {
        return $this->m3u8;
    }

    function getLogFile()
    {
        return $this->logFile;
    }

    function getJson()
    {
        return $this->json;
    }

    function getLive_transmitions_history_id()
    {
        return intval($this->live_transmitions_history_id);
    }

    function getLive_restreams_id()
    {
        return intval($this->live_restreams_id);
    }


    static function getLatest($live_transmitions_history_id, $live_restreams_id)
    {
        global $global;

        if (!static::isTableInstalled()) {
            return false;
        }

        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  live_transmitions_history_id = ? AND live_restreams_id = ? ORDER BY id DESC LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, 'ii', array($live_transmitions_history_id, $live_restreams_id));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    static function getURL($live_transmitions_history_id, $live_restreams_id, $live_restreams_logs_id, $action = 'log')
    {
        if (!empty($live_restreams_logs_id)) {
            $rlog = new Live_restreams_logs($live_restreams_logs_id);
            $url = $rlog->getRestreamer();
            $url = addQueryStringParameter($url, 'logFile', $rlog->getLogFile());
        } else {
            $url = Live::getRestreamer();
        }
        $url = str_replace('/var/www/html/AVideo/', '/', $url); // remove the AVideo path from the url
        if (!isValidURL($url)) {
            _error_log("Invalid restreamer URL {$url}, $live_transmitions_history_id, $live_restreams_id, $live_restreams_logs_id, $action");
            return false;
        }

        $url = addQueryStringParameter($url, 'tokenForAction', self::getToken($action, $live_transmitions_history_id, $live_restreams_id, $live_restreams_logs_id));

        return $url;
    }


    static function getURLFromTransmitionAndRestream($live_transmitions_history_id, $live_restreams_id, $action = 'log')
    {
        $latest = self::getLatest($live_transmitions_history_id, $live_restreams_id);
        if (empty($latest)) {
            $live_restreams_logs_id = 0;
        } else {
            $live_restreams_logs_id = $latest['id'];
        }
        //var_dump($live_transmitions_history_id, $live_restreams_id, $live_restreams_logs_id, $action);
        return self::getURL($live_transmitions_history_id, $live_restreams_id, $live_restreams_logs_id, $action);
    }

    static function getToken($action, $live_transmitions_history_id, $live_restreams_id, $live_restreams_logs_id, $users_id = 0)
    {
        $obj = new stdClass();
        $obj->action = $action;
        $obj->live_restreams_logs_id = $live_restreams_logs_id;
        $obj->live_transmitions_history_id = $live_transmitions_history_id;
        $obj->live_restreams_id = $live_restreams_id;
        $obj->time = time();
        $obj->users_id = empty($users_id) ? User::getId() : $users_id;

        $string = encryptString(json_encode($obj));
        return $string;
    }

    static function verifyToken($token, $secondsValid = 3600)
    {
        $string = decryptString($token);
        if (!empty($string)) {
            $obj = json_decode($string);
            if (!empty($obj)) {
                if ($obj->time > strtotime("-{$secondsValid} seconds")) {
                    return $obj;
                }
            }
        }
        return false;
    }
}
