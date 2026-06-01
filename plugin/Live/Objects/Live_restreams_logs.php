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

    /**
     * Returns all log records for a given restream destination, newest first.
     * If $users_id is provided (non-admin use), only records belonging to that user are returned.
     */
    static function getHistoryByRestream($live_restreams_id, $users_id = 0)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return [];
        }
        $live_restreams_id = intval($live_restreams_id);
        if (empty($live_restreams_id)) {
            return [];
        }
        $users_id = intval($users_id);

        if ($users_id > 0) {
            // Join with live_restreams to enforce ownership
            $sql = "SELECT lrl.*
                    FROM " . static::getTableName() . " lrl
                    INNER JOIN live_restreams lr ON lr.id = lrl.live_restreams_id
                    WHERE lrl.live_restreams_id = ?
                      AND lr.users_id = ?
                    ORDER BY lrl.id DESC";
            $res = sqlDAL::readSql($sql, 'ii', [$live_restreams_id, $users_id]);
        } else {
            $sql = "SELECT * FROM " . static::getTableName() . " WHERE live_restreams_id = ? ORDER BY id DESC";
            $res = sqlDAL::readSql($sql, 'i', [$live_restreams_id]);
        }

        $rows = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $rows ? $rows : [];
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

        // Re-validate host:port against admin-configured restreamer endpoints before
        // building the fetch URL. This is defense-in-depth: add.json.php already enforces
        // the allowlist at storage time, but a second check here ensures that any entry
        // that bypassed storage-time validation (e.g. pre-existing rows) cannot be fetched.
        // We do NOT use isSSRFSafeURL() because legitimate single-server deployments may
        // use http://localhost/ as the restreamer address, which that function blocks.
        $fetchHost   = strtolower(parse_url($url, PHP_URL_HOST));
        $fetchScheme = strtolower(parse_url($url, PHP_URL_SCHEME));
        $fetchPort   = parse_url($url, PHP_URL_PORT) ?: ($fetchScheme === 'https' ? 443 : 80);
        $fetchKey    = "{$fetchHost}:{$fetchPort}";

        $allowedKeys = [];
        $primaryURL = Live::getRestreamer();
        if (!empty($primaryURL)) {
            $h = strtolower(parse_url($primaryURL, PHP_URL_HOST));
            $p = parse_url($primaryURL, PHP_URL_PORT) ?: (strtolower(parse_url($primaryURL, PHP_URL_SCHEME)) === 'https' ? 443 : 80);
            $allowedKeys[] = "{$h}:{$p}";
        }
        $liveObj = AVideoPlugin::getObjectData('Live');
        if (!empty($liveObj->useLiveServers)) {
            require_once dirname(__FILE__) . '/Live_servers.php';
            $servers = Live_servers::getAllActive();
            if (!empty($servers)) {
                foreach ($servers as $row) {
                    if (empty($row['restreamerURL'])) {
                        continue;
                    }
                    $h = strtolower(parse_url($row['restreamerURL'], PHP_URL_HOST));
                    $p = parse_url($row['restreamerURL'], PHP_URL_PORT) ?: (strtolower(parse_url($row['restreamerURL'], PHP_URL_SCHEME)) === 'https' ? 443 : 80);
                    $allowedKeys[] = "{$h}:{$p}";
                }
            }
        }
        if (empty($allowedKeys) || !in_array($fetchKey, $allowedKeys, true)) {
            _error_log("getURL: restreamer URL host not in allowlist. fetch={$fetchKey} allowed=" . implode(',', $allowedKeys));
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
