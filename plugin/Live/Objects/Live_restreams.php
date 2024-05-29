<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

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
        $this->name = $name;
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
