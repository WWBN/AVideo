<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../standAloneFiles/restreamProfiles.php';

class Live_restreams extends ObjectYPT {

    protected $id;
    protected $name;
    protected $stream_url;
    protected $stream_key;
    protected $status;
    protected $parameters;
    protected $users_id;

    public static function getSearchFieldsNames() {
        return ['name', 'stream_url', 'stream_key', 'parameters'];
    }

    public static function getTableName() {
        return 'live_restreams';
    }

    public function setId($id) {
        $this->id = intval($id);
    }

    public function setName($name) {
        // stored raw name reaches the admin DataTable unescaped, so sanitize on write
        $this->name = xss_esc($name);
    }

    public function setStream_url($stream_url) {
        $this->stream_url = $stream_url;
    }

    public function setStream_key($stream_key) {
        $this->stream_key = $stream_key;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setParameters($parameters) {
        $this->parameters = $parameters;
    }

    public function setUsers_id($users_id) {
        $this->users_id = intval($users_id);
    }

    public function getId() {
        return intval($this->id);
    }

    public function getName() {
        return $this->name;
    }

    public function getStream_url() {
        return $this->stream_url;
    }

    public function getStream_key() {
        return $this->stream_key;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getParameters() {
        return $this->parameters;
    }

    public function getUsers_id() {
        return intval($this->users_id);
    }

    public static function getAllFromUser($users_id, $status = 'a') {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }

        $users_id = intval($users_id);
        if (empty($users_id)) {
            return false;
        }

        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE users_id = $users_id ";

        if (!empty($status)) {
            $sql .= " AND status = '$status' ";
        }

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            foreach ($fullData as $row) {
                $row['restreamsToken'] = encryptString($row['id']);
                $row['display_name'] = getWordOrIcon($row['name'], 'fa-2x');
                $row['token_expired'] = self::checkIfTokenIsExpired($row['parameters']);
                $row['provider'] = self::getProviderName($row['parameters']);
                if ($row['token_expired']->needToRevalidate) {
                    $row['revalidateButton'] = '<button class="btn btn-primary btn-xs mediumSocialIcon" onclick="openRestream(\''.$row['provider'].'\');"><i class="fas fa-sync"></i> '.__("Revalidate").'</button>';
                } else {
                    $row['revalidateButton'] = '<i class="fas fa-check text-success"></i>';
                }
                $rows[] = $row;
            }
        }
        return $rows;
    }

    /**
     * Restreams actually started (has at least one live_restreams_logs row) for a given
     * live session, regardless of which account owns the restream destination. Needed because a
     * restream destination can be configured by an admin account while the live itself streams
     * under a different account (see LiveRestreamWatchdog::run()).
     */
    public static function getAllFromLiveTransmitionHistory($live_transmitions_history_id, $status = 'a') {
        if (!static::isTableInstalled()) {
            return [];
        }
        $live_transmitions_history_id = intval($live_transmitions_history_id);
        if (empty($live_transmitions_history_id)) {
            return [];
        }

        $sql = "SELECT lr.* FROM " . static::getTableName() . " lr
                INNER JOIN (
                    SELECT DISTINCT live_restreams_id FROM live_restreams_logs WHERE live_transmitions_history_id = ?
                ) x ON x.live_restreams_id = lr.id";
        $formats = 'i';
        $values = [$live_transmitions_history_id];
        if (!empty($status)) {
            $sql .= " WHERE lr.status = ?";
            $formats .= 's';
            $values[] = $status;
        }
        $res = sqlDAL::readSql($sql, $formats, $values);
        $rows = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $rows ? $rows : [];
    }

    // YouTube broadcast that restream.ypt.me created for this destination in this live session.
    // A reconnection of the same session sends it back so YouTube keeps the same video while the
    // broadcast is still live. Kept in the temporary cache: if it is lost, a new broadcast is
    // created, which is what always happened before.
    private static function getYouTubeBroadcastFile($live_restreams_id, $liveTransmitionHistory_id) {
        $live_restreams_id = intval($live_restreams_id);
        $liveTransmitionHistory_id = intval($liveTransmitionHistory_id);
        if ($live_restreams_id <= 0 || $liveTransmitionHistory_id <= 0) {
            return '';
        }
        return ObjectYPT::getTmpCacheDir() . 'LiveRestreamYouTubeBroadcast' . DIRECTORY_SEPARATOR . "{$live_restreams_id}_{$liveTransmitionHistory_id}.json";
    }

    public static function getYouTubeBroadcastIdToReuse($live_restreams_id, $liveTransmitionHistory_id) {
        $file = self::getYouTubeBroadcastFile($live_restreams_id, $liveTransmitionHistory_id);
        if (empty($file) || !file_exists($file)) {
            return '';
        }
        global $global;
        $maxAge = isset($global['objectCacheMaxAge']) ? max(60, intval($global['objectCacheMaxAge'])) : 259200;
        $modified = @filemtime($file);
        if ($modified === false || $modified < time() - $maxAge) {
            return '';
        }
        $data = json_decode((string) @file_get_contents($file), true);
        if (!is_array($data) || !isValidYouTubeBroadcastId($data['broadcast_id'] ?? null)) {
            return '';
        }
        return $data['broadcast_id'];
    }

    public static function saveYouTubeBroadcastFromRestreamerResponse($live_restreams_id, $liveTransmitionHistory_id, $response) {
        $broadcastId = getYouTubeBroadcastIdFromRestreamerResponse($response);
        $file = self::getYouTubeBroadcastFile($live_restreams_id, $liveTransmitionHistory_id);
        if (empty($broadcastId) || empty($file)) {
            return false;
        }
        require_once __DIR__ . '/../../../objects/ObjectCacheMaintenance.php';
        ObjectYPT::scheduleCacheMaintenance();
        global $global;
        $maxBytes = isset($global['objectCacheMaxBytes']) ? max(1, intval($global['objectCacheMaxBytes'])) : ObjectCacheMaintenance::DEFAULT_MAX_BYTES;
        $maxFiles = isset($global['objectCacheMaxFiles']) ? max(1, intval($global['objectCacheMaxFiles'])) : ObjectCacheMaintenance::DEFAULT_MAX_FILES;
        return ObjectCacheMaintenance::write(ObjectYPT::getTmpCacheDir(), $file,
            json_encode(['broadcast_id' => $broadcastId, 'saved_at' => time()]), $maxBytes, $maxFiles) !== false;
    }

    private static function getProviderName($parameters) {
        if (is_string($parameters)) {
            $parameters = object_to_array(json_decode($parameters));
        }

        if (!empty($parameters['restream.ypt.me'])) {
            foreach ($parameters['restream.ypt.me'] as $key => $value) {
                if (preg_match('/(youtube|facebook|twitch)/i', $key)) {
                    return $key;
                }
            }
        }

        return false;
    }

    private static function checkIfTokenIsExpired($parameters) {
        if (is_string($parameters)) {
            $parameters = object_to_array(json_decode($parameters));
        }

        $obj = new stdClass();
        $obj->isExpired = false;
        $obj->now = time();
        $obj->expires_at = false;
        $obj->msg = '';
        $obj->parameters = false;
        $obj->willAutoRenew = false;
        if (empty($parameters['restream.ypt.me'])) {
            $obj->msg = 'Not a restreamer object';
            return $obj;
        } else {
            $obj->parameters = $parameters['restream.ypt.me'];
            foreach ($obj->parameters as $value) {
                if (!empty($value['expires_at'])) {
                    $obj->expires_at = $value['expires_at'];
                    $obj->willAutoRenew = !empty($value['refresh_token']);
                    $obj->isExpired = $obj->expires_at < $obj->now;
                    $obj->needToRevalidate = $obj->isExpired && !$obj->willAutoRenew;
                    return $obj;
                }
            }
        }
        $obj->isExpired = true;

        return $obj;
    }

    public function save() {
        $rows = self::getAllFromUser($this->users_id, '');
        foreach ($rows as $row) {
            if ($row['name'] == $this->name || $this->stream_key !== 'Automatic') {
                if ($row['stream_key'] == $this->stream_key && $row['stream_url'] == $this->stream_url) {
                    $this->id = $row['id'];
                    break;
                }
            }
        }
        return parent::save();
    }

}
