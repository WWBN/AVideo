<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Publisher_user_preferences extends ObjectYPT
{

    protected $id, $users_id, $publisher_social_medias_id, $preferred_profile, $timezone, $json;

    static function getSearchFieldsNames()
    {
        return array('preferred_profile', 'timezone', 'json');
    }

    static function getTableName()
    {
        return 'publisher_user_preferences';
    }

    public static function getAll()
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT psm.*, pup.* FROM  " . static::getTableName() . " pup LEFT JOIN publisher_social_medias psm  ON publisher_social_medias_id = psm.id  WHERE 1=1 ";

        $sql .= self::getSqlFromPost('pup.');
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $fullData;
    }

    public static function getAllFromUsersId($users_id)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT psm.*, pup.* FROM  " . static::getTableName() . " pup LEFT JOIN publisher_social_medias psm  ON publisher_social_medias_id = psm.id  WHERE users_id= ? ";

        $sql .= self::getSqlFromPost('pup.');
        $res = sqlDAL::readSql($sql, 'i', [$users_id]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        foreach ($fullData as $key => $value) {
            $fullData[$key] = self::addRowDetails($fullData[$key]);
        }
        sqlDAL::close($res);
        return $fullData;
    }

    function setId($id)
    {
        $this->id = intval($id);
    }

    function setUsers_id($users_id)
    {
        $this->users_id = intval($users_id);
    }

    function setPublisher_social_medias_id($publisher_social_medias_id)
    {
        $this->publisher_social_medias_id = intval($publisher_social_medias_id);
    }

    function setPreferred_profile($preferred_profile)
    {
        $this->preferred_profile = $preferred_profile;
    }

    function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    function setJson($json)
    {
        if(!is_string($json)){
            $json = json_encode($json);
        }
        $this->json = $json;
    }

    function getId()
    {
        return intval($this->id);
    }

    function getUsers_id()
    {
        return intval($this->users_id);
    }

    function getPublisher_social_medias_id()
    {
        return intval($this->publisher_social_medias_id);
    }

    function getPreferred_profile()
    {
        return $this->preferred_profile;
    }

    function getTimezone()
    {
        return $this->timezone;
    }

    function getJson()
    {
        return $this->json;
    }

    function getProviderName()
    {
        $p = new Publisher_social_medias($this->publisher_social_medias_id);
        return $p->getName();
    }

    static function getFromProfileName($users_id, $publisher_social_medias_id, $preferred_profile)
    {
        global $global;
        if (!class_exists('sqlDAL')) {
            return false;
        }
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = ? AND  publisher_social_medias_id = ? AND  preferred_profile = ? LIMIT 1";
        //var_dump($sql, $id);
        $res = sqlDAL::readSql($sql, "iis", [$users_id, $publisher_social_medias_id, $preferred_profile], true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public static function addRowDetails($data){
        $data['jsonObject'] = json_decode($data['json']);
        $data['accessTokenExpired'] = true;
        $data['canRefreshAccessToken'] = true;
        if (!empty($data['jsonObject']->{'restream.ypt.me'})) {
            $accessToken = $data['jsonObject']->{'restream.ypt.me'};
            //$fullData[$key]['_accessToken'] = $accessToken->accessToken;
            if (empty($accessToken->expires->expires_at)) {
                $data['accessTokenExpired'] = false;
                $data['expires_at_human'] = __('Not defined');
            } else {
                $data['accessTokenExpired'] = $accessToken->expires->expires_at < time();
                $data['expires_at_human'] = $accessToken->expires->expires_at_human;
            }
            $data['canRefreshAccessToken'] = !empty($accessToken->accessToken->refresh_token);
            $data['details'] = SocialMediaPublisher::getProiderItem($data['name']);
        }
        return $data;
    }


    public static function getFromUsersIdAndProvider($users_id, $provider)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT psm.*, pup.* FROM  " . static::getTableName() . " pup LEFT JOIN publisher_social_medias psm  ON publisher_social_medias_id = psm.id  WHERE users_id= ? AND name = ? ";

        $sql .= self::getSqlFromPost('pup.');
        $res = sqlDAL::readSql($sql, 'is', [$users_id, $provider], true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $data = self::addRowDetails($data);
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    
    static function getFromDb($id, $refreshCache = false)
    {
        global $global;
        if(!class_exists('sqlDAL')){
            return false;
        }
        $id = intval($id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  id = ? LIMIT 1";
        //var_dump($sql, $id);
        $res = sqlDAL::readSql($sql, "i", [$id], $refreshCache);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $data = self::addRowDetails($data);
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public function save()
    {
        if (empty($this->id)) {
            $row = self::getFromProfileName($this->users_id, $this->publisher_social_medias_id, $this->preferred_profile);
            if (!empty($row)) {
                $this->id = $row['id'];
            }
        }
        return parent::save();
    }
}
