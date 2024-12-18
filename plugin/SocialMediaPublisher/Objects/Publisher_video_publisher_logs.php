<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Publisher_video_publisher_logs extends ObjectYPT
{

    protected $id, $publish_datetimestamp, $status, $details, $videos_id, $users_id,
        $publisher_social_medias_id, $timezone;

    const STATUS_UNVERIFIED = 'u';
    const STATUS_VERIFIED = 'v';
    const STATUS_ACTIVE = 'a';
    const STATUS_INACTIVE = 'i';
    const STATUS_PROCESSING = 'p';

    static function getSearchFieldsNames()
    {
        return array('details', 'timezone');
    }

    static function getTableName()
    {
        return 'publisher_video_publisher_logs';
    }

    static function getAllVideos()
    {
        global $global;
        $table = "videos";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            /**
             * 
             * @var array $global
             * @var object $global['mysqli'] 
             */
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    static function getAllUsers()
    {
        global $global;
        $table = "users";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            /**
             * 
             * @var array $global
             * @var object $global['mysqli'] 
             */
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    static function getAllPublisher_social_medias()
    {
        global $global;
        $table = "publisher_social_medias";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            /**
             * 
             * @var array $global
             * @var object $global['mysqli'] 
             */
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }


    function setId($id)
    {
        $this->id = intval($id);
    }

    function setPublish_datetimestamp($publish_datetimestamp)
    {
        $this->publish_datetimestamp = $publish_datetimestamp;
    }

    function setStatus($status)
    {
        $this->status = $status;
    }

    function setDetails($details)
    {
        if (!is_string($details)) {
            $details = json_encode($details);
        }
        $this->details = $details;
    }

    function setVideos_id($videos_id)
    {
        $this->videos_id = intval($videos_id);
    }

    function setUsers_id($users_id)
    {
        $this->users_id = intval($users_id);
    }

    function setPublisher_social_medias_id($publisher_social_medias_id)
    {
        $this->publisher_social_medias_id = intval($publisher_social_medias_id);
    }

    function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }


    function getId()
    {
        return intval($this->id);
    }

    function getPublish_datetimestamp()
    {
        return $this->publish_datetimestamp;
    }

    function getStatus()
    {
        return $this->status;
    }

    function getDetails()
    {
        return $this->details;
    }

    function getVideos_id()
    {
        return intval($this->videos_id);
    }

    function getUsers_id()
    {
        return intval($this->users_id);
    }

    function getPublisher_social_medias_id()
    {
        return intval($this->publisher_social_medias_id);
    }

    function getTimezone()
    {
        return $this->timezone;
    }

    public static function getAllFromVideosId($videos_id)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT psm.*, pvpl.* FROM  " . static::getTableName() . " pvpl LEFT JOIN publisher_social_medias psm ON publisher_social_medias_id = psm.id WHERE videos_id = ? ";

        $sql .= self::getSqlFromPost('pvpl.');
        $res = sqlDAL::readSql($sql, 'i', [$videos_id]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        foreach ($fullData as $key => $value) {
            $fullData[$key] = self::getInfo($fullData[$key]);
        }
        return $fullData;
    }

    static function getInfo($row)
    {
        global $global;

        $row['publish'] = date('Y-m-d H:i:s', $row['publish_datetimestamp']);
        $row['json'] = json_decode($row['details']);
        $row['provider'] = SocialMediaPublisher::getProiderItem($row['name']);
        $row['msg'] = '';
        if (!empty($row['json']->response)) {
            $msg = array();
            $error = SocialUploader::getErrorMsg($row['json']->response);
            if (!empty($error)) {
                $msg[] = $error;
            }
            //var_dump($row['name'], SocialMediaPublisher::SOCIAL_TYPE_YOUTUBE["name"], $row['json']->response->id);exit;
            switch ($row['name']) {
                case SocialMediaPublisher::SOCIAL_TYPE_YOUTUBE["name"]:
                    if (!empty($row['json']->response->id)) {
                        $link = "https://youtu.be/" . $row['json']->response->id;
                        $msg[] = "<a href='{$link}' target='_blank'>{$link}</a>";
                    }
                    break;
                case SocialMediaPublisher::SOCIAL_TYPE_LINKEDIN["name"]:
                    if (!empty($row['json']->response->publishResult) && !empty($row['json']->response->publishResult->xRestLiId)) {
                        $link = "https://www.linkedin.com/feed/update/" . $row['json']->response->publishResult->xRestLiId;
                        $msg[] = "<a href='{$link}' target='_blank'>{$link}</a>";
                    }
                    break;
                case SocialMediaPublisher::SOCIAL_TYPE_FACEBOOK["name"]:
                    if (!empty($row['json']->response->VideoUploadResponse) && !empty($row['json']->response->VideoUploadResponse->id)) {
                        $link = "https://www.facebook.com/watch/?v=" . $row['json']->response->VideoUploadResponse->id;
                        $msg[] = "<a href='{$link}' target='_blank'>{$link}</a>";
                    }
                    break;
                case SocialMediaPublisher::SOCIAL_TYPE_INSTAGRAM["name"]:
                    if (!empty($row['json']->mediaResponse->permalink)) {
                        $msg[] = "<a href='{$row['json']->mediaResponse->permalink}' target='_blank'>{$row['json']->mediaResponse->permalink}</a>";
                    } else if ($row['status'] === self::STATUS_UNVERIFIED) {
                        $msg[] = '<i class="fa fa-spinner fa-spin"></i> <strong>Video is being processed:</strong> Your video is currently being processed for publishing on Instagram. Please wait.';
                        $msg[] = '<button class="btn btn-primary btn-xs" onclick="checkInstagram(\'' . $row['json']->response->accessToken . '\', \'' . $row['json']->response->containerId . '\', \'' . $row['json']->response->instagramAccountId . '\')"><i class="fas fa-check-circle"></i> ' . __('Check now') . '</button>';
                    } elseif ($row['status'] === self::STATUS_VERIFIED) {
                        $msg[] = '<i class="fa fa-check-circle"></i> <strong>Video successfully published:</strong> Your video has been verified and uploaded to Instagram.';
                    } else {
                        $msg[] = '<i class="fa fa-exclamation-triangle"></i> <strong>status:</strong> ' . $row['status'] . '';
                    }

                    break;
            }
            $row['msg'] = implode('<br>', $msg);
        }
        return $row;
    }

    public static function getTotalFromVideosId($videos_id)
    {
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        if (!static::isTableInstalled()) {
            return 0;
        }
        $sql = "SELECT id FROM  " . static::getTableName() . " WHERE videos_id = ? ";
        $sql .= self::getSqlSearchFromPost();
        $res = sqlDAL::readSql($sql, 'i', [$videos_id]);
        $countRow = sqlDAL::num_rows($res);
        sqlDAL::close($res);
        return $countRow;
    }

    public function save()
    {
        if (empty($this->status)) {
            $this->status = self::STATUS_UNVERIFIED;
        }
        return parent::save();
    }
}
