<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../standAloneFiles/restreamProfiles.php';
require_once dirname(__FILE__) . '/LiveRestreamDestinationInfo.php';

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
                if (!empty($row['token_expired']->needToRevalidate)) {
                    $row['revalidateButton'] = '<button class="btn btn-primary btn-xs mediumSocialIcon" onclick="openRestream(\''.$row['provider'].'\');"><i class="fas fa-sync"></i> '.__("Revalidate").'</button>';
                } else {
                    $row['revalidateButton'] = '<i class="fas fa-check text-success"></i>';
                }
                $rows[] = $row;
            }
        }
        try {
            return self::addDestinationStatus($rows);
        } catch (\Throwable $th) {
            // Status hints are informational: never let them break starting a restream.
            _error_log('Live_restreams::getAllFromUser: status hints failed: ' . $th->getMessage(), AVideoLog::$WARNING);
            return $rows;
        }
    }

    /**
     * Adds what the owner should know about each destination: the channel it really sends to,
     * the result of the last live and warnings (same channel twice, expired connection, channel
     * that cannot go live). Read-only and silent, so it is safe for scheduled/automatic lives.
     */
    public static function addDestinationStatus($rows) {
        if (!is_array($rows)) {
            return $rows;
        }
        $channels = [];
        $statuses = [];
        foreach ($rows as $key => $row) {
            $id = intval($row['id'] ?? 0);
            $statuses[$id] = self::getDestinationStatus($id);
            $channel = LiveRestreamDestinationInfo::getChannelFromParameters($row['parameters'] ?? '');
            $provider = LiveRestreamDestinationInfo::getConnectedProvider($row['parameters'] ?? '');
            if (empty($channel) && $provider !== '') {
                // read later by channelInfo.json.php for destinations saved before the channel was stored
                $channel = LiveRestreamDestinationInfo::normalizeChannel($statuses[$id]['channel'] ?? null, $provider);
            }
            $channels[$id] = $channel;
        }
        $sameChannel = LiveRestreamDestinationInfo::getSameChannelDestinations($channels);
        foreach ($rows as $key => $row) {
            $id = intval($row['id'] ?? 0);
            $channel = $channels[$id];
            $status = $statuses[$id];
            $isConnected = LiveRestreamDestinationInfo::getConnectedProvider($row['parameters'] ?? '') !== '';
            $lookupFailed = !empty($status['channel_lookup_failed_at']) && $status['channel_lookup_failed_at'] > time() - 3600;
            $tokenExpired = is_object($row['token_expired'] ?? null) && !empty($row['token_expired']->needToRevalidate);
            $hints = LiveRestreamDestinationInfo::getHints($row, $channel, $status['last'] ?? null, $tokenExpired, $sameChannel[$id] ?? [], $lookupFailed);
            $rows[$key]['channel'] = $channel;
            $rows[$key]['channel_label'] = LiveRestreamDestinationInfo::getChannelLabel($channel);
            $rows[$key]['channel_html'] = LiveRestreamDestinationInfo::getChannelHtml($channel);
            $rows[$key]['status_hints'] = $hints;
            $rows[$key]['status_html'] = LiveRestreamDestinationInfo::getHintsHtml($hints, $tokenExpired ? $row['revalidateButton'] : '');
            $rows[$key]['needs_channel_lookup'] = $isConnected && empty($channel) && !$lookupFailed;
            $rows[$key]['same_channel_as'] = $sameChannel[$id] ?? [];
        }
        return $rows;
    }

    // Per-destination status kept in the temporary cache (last key request result, channel read
    // later for destinations connected before the channel was saved). Losing it only hides hints.
    private static function getDestinationStatusFile($live_restreams_id) {
        $live_restreams_id = intval($live_restreams_id);
        if ($live_restreams_id <= 0) {
            return '';
        }
        return ObjectYPT::getTmpCacheDir() . 'LiveRestreamDestinationStatus' . DIRECTORY_SEPARATOR . "{$live_restreams_id}.json";
    }

    public static function getDestinationStatus($live_restreams_id) {
        $file = self::getDestinationStatusFile($live_restreams_id);
        if (empty($file) || !is_file($file)) {
            return [];
        }
        $data = json_decode((string) @file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }

    public static function saveDestinationStatus($live_restreams_id, $changes) {
        $file = self::getDestinationStatusFile($live_restreams_id);
        if (empty($file) || !is_array($changes)) {
            return false;
        }
        $data = array_merge(self::getDestinationStatus($live_restreams_id), $changes);
        $content = json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);
        if ($content === false) {
            return false;
        }
        require_once __DIR__ . '/../../../objects/ObjectCacheMaintenance.php';
        ObjectYPT::scheduleCacheMaintenance(); // the first write waits for the cache size scan
        global $global;
        $maxBytes = isset($global['objectCacheMaxBytes']) ? max(1, intval($global['objectCacheMaxBytes'])) : ObjectCacheMaintenance::DEFAULT_MAX_BYTES;
        $maxFiles = isset($global['objectCacheMaxFiles']) ? max(1, intval($global['objectCacheMaxFiles'])) : ObjectCacheMaintenance::DEFAULT_MAX_FILES;
        // The cache lock is briefly held while maintenance runs; try again before giving up.
        for ($attempt = 0; $attempt < 3; $attempt++) {
            if (ObjectCacheMaintenance::write(ObjectYPT::getTmpCacheDir(), $file, $content, $maxBytes, $maxFiles) !== false) {
                return true;
            }
            usleep(150000);
        }
        _error_log("Live_restreams::saveDestinationStatus: could not save the status of live_restreams_id={$live_restreams_id}", AVideoLog::$WARNING);
        return false;
    }

    public static function deleteDestinationStatus($live_restreams_id) {
        $file = self::getDestinationStatusFile($live_restreams_id);
        if (!empty($file) && is_file($file)) {
            @unlink($file);
        }
    }

    /**
     * Live sessions (not finished, active in the last 2 days) in which this destination was
     * started. Used to stop its relay before the destination is deleted: deleting the row alone
     * left FFmpeg sending the live to the old channel (TIC, 2026-11-21).
     */
    public static function getUnfinishedHistoriesWithRelay($live_restreams_id) {
        $live_restreams_id = intval($live_restreams_id);
        if ($live_restreams_id <= 0) {
            return [];
        }
        $sql = "SELECT DISTINCT l.live_transmitions_history_id AS id FROM live_restreams_logs l
                INNER JOIN live_transmitions_history h ON h.id = l.live_transmitions_history_id
                WHERE l.live_restreams_id = ? AND h.finished IS NULL AND h.modified > DATE_SUB(NOW(), INTERVAL 2 DAY)";
        $res = sqlDAL::readSql($sql, 'i', [$live_restreams_id]);
        $rows = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return array_values(array_filter(array_map('intval', array_column($rows ? $rows : [], 'id'))));
    }

    /**
     * Asks the restreamer to stop this destination in every unfinished live.
     * Returns ['stopped' => relays really killed, 'failed' => relays the restreamer could not stop].
     */
    public static function stopRelaysOfDestination($live_restreams_id) {
        require_once __DIR__ . '/Live_restreams_logs.php';
        $result = ['stopped' => 0, 'failed' => 0];
        foreach (self::getUnfinishedHistoriesWithRelay($live_restreams_id) as $historyId) {
            $url = Live_restreams_logs::getURLFromTransmitionAndRestream($historyId, intval($live_restreams_id), 'stop');
            if (empty($url)) {
                _error_log("Live_restreams::stopRelaysOfDestination: no restreamer URL for live_restreams_id={$live_restreams_id} history={$historyId}", AVideoLog::$WARNING);
                continue;
            }
            $response = url_get_contents($url, '', 20, false, false, false);
            $json = json_decode((string) $response);
            _error_log("Live_restreams::stopRelaysOfDestination: live_restreams_id={$live_restreams_id} history={$historyId} response=" . substr((string) $response, 0, 300));
            if (!is_object($json)) {
                continue; // restreamer unreachable: nothing more can be done from here
            }
            if (!empty($json->error)) {
                $result['failed']++;
            } elseif (!empty($json->remoteKill) || !empty($json->killIfIsRunning)) {
                $result['stopped']++;
            }
        }
        return $result;
    }

    /**
     * An existing destination of the same owner connected to the same channel/page, so that
     * connecting the channel again replaces its connection instead of adding a second copy.
     */
    public function getIdOfSameChannelDestination() {
        $channel = LiveRestreamDestinationInfo::getChannelFromParameters($this->parameters);
        if (empty($channel) || empty($this->users_id)) {
            return 0;
        }
        $rows = self::getAllFromUser($this->users_id, '');
        foreach ((array) $rows as $row) {
            if (intval($row['id']) === intval($this->id)) {
                continue;
            }
            $rowChannel = $row['channel'] ?? null;
            if (!empty($rowChannel['id']) && $rowChannel['id'] === $channel['id']
                && ($rowChannel['provider'] ?? 'youtube') === $channel['provider']) {
                return intval($row['id']);
            }
        }
        return 0;
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

    /**
     * Recognizes a restream.ypt.me answer that failed because the account connection must be
     * renewed (access expired, revoked, or granted without the permission to go live) and returns
     * a plain-text message the channel owner can act on. Returns null for any other answer, which
     * is then shown as before.
     */
    public static function getReconnectInfoFromRestreamerResponse($response, $destinationName, $parameters) {
        if (!is_object($response) || empty($response->error)) {
            return null;
        }
        $details = array();
        foreach (array($response->msg ?? '', $response->rawData ?? '') as $value) {
            if (is_string($value) || is_object($value) || is_array($value)) {
                $details[] = is_string($value) ? $value : json_encode($value);
            }
        }
        $details = trim(implode("\n", $details));
        // A refresh warning does not invalidate a usable access token. Prefer the actual
        // provider failure (quota, eligibility, network, etc.) whenever one was returned.
        if ($details === '') {
            $accessToken = object_to_array($response->access_token ?? array());
            $value = $accessToken['msg'] ?? '';
            $details = is_string($value) ? $value : json_encode($value);
        }

        if (preg_match('/insufficient authentication scopes|ACCESS_TOKEN_SCOPE_INSUFFICIENT/i', $details)) {
            $reason = 'permission';
        } else if (preg_match('/invalid_grant|UNAUTHENTICATED|Invalid Credentials|"(?:code|status)"\s*:\s*401(?=\s*[,}])|Invalid OAuth token|Error validating access token|Session has expired|has not authorized application|No refresh token available/i', $details)) {
            $reason = 'expired';
        } else {
            return null;
        }

        $labels = array('youtube' => 'YouTube', 'facebook' => 'Facebook', 'twitch' => 'Twitch');
        if (is_string($parameters)) {
            $parameters = json_decode($parameters);
        }
        $provider = strtolower((string) self::getProviderName(object_to_array($parameters)));
        if (empty($labels[$provider])) {
            return null;
        }
        $label = $labels[$provider];
        $destinationName = html_entity_decode($destinationName, ENT_QUOTES, 'UTF-8');

        if ($reason === 'permission') {
            $msg = sprintf(__('%s did not give "%s" permission to go live. This happens when a permission box is left unchecked while connecting the account.'), $label, $destinationName);
        } else {
            $msg = sprintf(__('%s is no longer accepting the connection used by "%s". This happens when the access expires or is removed in the account settings.'), $label, $destinationName);
        }
        // one paragraph per line: the reconnect dialog shows them apart, plain alerts as one text
        $msg .= "\n" . sprintf(__('To fix it, reconnect your %s account with the %s button in the Restream panel and keep every permission box checked. Then start this destination again.'), $label, $label);
        $msg .= "\n" . __('If you sign in with a different account, delete the old destination from the list.');

        $info = new stdClass();
        $info->reason = $reason;
        $info->provider = $provider;
        $info->providerLabel = $label;
        $info->msg = $msg;
        return $info;
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
        if (empty($this->id)) {
            // Connecting the same channel/page again updates the existing destination.
            $sameChannelId = $this->getIdOfSameChannelDestination();
            if (!empty($sameChannelId)) {
                $this->id = $sameChannelId;
            }
        }
        $rows = self::getAllFromUser($this->users_id, '');
        foreach ((array) $rows as $row) {
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
