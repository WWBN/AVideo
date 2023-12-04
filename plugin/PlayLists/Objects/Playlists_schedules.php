<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Playlists_schedules extends ObjectYPT
{

    const STATUS_ACTIVE = 'a';
    const STATUS_INACTIVE = 'a';
    const STATUS_EXECUTING = 'e';
    const STATUS_COMPLETE = 'c';
    const STATUS_FAIL = 'f';

    const STATUS_TEXT = array(
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_EXECUTING => 'Executing',
        self::STATUS_COMPLETE => 'Complete',
        self::STATUS_FAIL => 'Fail',
    );

    protected $id, $playlists_id, $name, $description, $status, $loop, $start_datetime, $finish_datetime, $repeat, $parameters;

    static $REPEAT_MONTHLY = 'm';
    static $REPEAT_WEEKLY = 'w';
    static $REPEAT_DAILY = 'd';
    static $REPEAT_NEVER = 'n';

    static $REPEAT_TEXT = array('m' => 'Monthly', 'w' => 'Weekly', 'd' => 'Daily', 'n' => 'Never');

    static function getSearchFieldsNames()
    {
        return array('name', 'description', 'parameters');
    }

    static function getTableName()
    {
        return 'playlists_schedules';
    }

    function setId($id)
    {
        $this->id = intval($id);
    }

    function setPlaylists_id($playlists_id)
    {
        $this->playlists_id = intval($playlists_id);
    }

    function setName($name)
    {
        $this->name = $name;
    }

    function setDescription($description)
    {
        $this->description = $description;
    }

    function setStatus($status)
    {
        $this->status = $status;
    }

    function setLoop($loop)
    {
        $this->loop = intval($loop);
    }

    function setStart_datetime($start_datetime)
    {
        if (!is_numeric($start_datetime)) {
            $start_datetime = strtotime($start_datetime);
        }
        $this->start_datetime = $start_datetime;
    }

    function setFinish_datetime($finish_datetime)
    {
        if (!is_numeric($finish_datetime)) {
            $finish_datetime = strtotime($finish_datetime);
        }
        $this->finish_datetime = $finish_datetime;
    }

    function setRepeat($repeat)
    {
        $this->repeat = $repeat;
    }

    function setParameters($parameters)
    {
        if (!is_string($parameters)) {
            $parameters = _json_encode($parameters);
        }
        $this->parameters = $parameters;
    }


    function getId()
    {
        return intval($this->id);
    }

    function getPlaylists_id()
    {
        return intval($this->playlists_id);
    }

    function getName()
    {
        return $this->name;
    }

    function getDescription()
    {
        return $this->description;
    }

    function getStatus()
    {
        return $this->status;
    }

    function getLoop()
    {
        return intval($this->loop);
    }

    function getStart_datetime()
    {
        return $this->start_datetime;
    }

    function getFinish_datetime()
    {
        return $this->finish_datetime;
    }

    function getRepeat()
    {
        return $this->repeat;
    }

    function getParameters()
    {
        return $this->parameters;
    }

    public static function getAllFromStatus($status)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status = ? ";

        $res = sqlDAL::readSql($sql, 's', [$status]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $fullData;
    }

    public static function getAllActive()
    {
        return self::getAllFromStatus(self::STATUS_ACTIVE);
    }

    public static function getAllExecuting()
    {
        return self::getAllFromStatus(self::STATUS_EXECUTING);
    }

    public function save()
    {
        if (empty($this->status)) {
            $this->status = self::STATUS_ACTIVE;
        }
        return parent::save();
    }

    static function getPlaying($playlists_schedules_id)
    {
        $ps = new Playlists_schedules($playlists_schedules_id);

        $plsp = new PlaylistsSchedulesParameters();
        $plsp->playlists_id = $ps->getPlaylists_id();
        $plsp->playlists_schedules_id = $playlists_schedules_id;
        $plsp->startTime = time();

        if (empty($plsp->playlists_id)) {
            $plsp->msg = 'playlists_id not found';
            return $plsp;
        }

        $videosArrayId = PlayList::getVideosIdFromPlaylist($plsp->playlists_id);
        if (empty($videosArrayId)) {
            $plsp->msg = 'videosArrayId is empty';
            $ps->setStatus(self::STATUS_FAIL);
            $ps->setParameters($plsp);
            $ps->save();
            return $plsp;
        }

        $cleanVideosArrayId = array();

        foreach ($videosArrayId as $key => $value) {
            $video = new Video('', '', $value);
            if ($video->getType() == Video::$videoTypeVideo) {
                $cleanVideosArrayId[] = $value;
            }
        }

        if (empty($cleanVideosArrayId)) {
            $plsp->msg = 'There is no valid videos in this playlist';
            $ps->setStatus(self::STATUS_FAIL);
            $ps->setParameters($plsp);
            $ps->save();
            return $plsp;
        }

        $plsp->totalVideos = count($cleanVideosArrayId);

        $parametersText = $ps->getParameters();
        if (!empty($parametersText)) {
            $plsp = _json_decode($parametersText);
            $plsp->fromParameters = true;
            foreach ($cleanVideosArrayId as $key => $value) {
                if ($plsp->current_videos_id == $value) {
                    $plsp->current_videos_id_index = $key;
                    break;
                }
            }
            if ($plsp->current_videos_id_index + 1 < $plsp->totalVideos) {
                $plsp->current_videos_id_index += 1;
            } else {
                if (empty($ps->getLoop())) {
                    $plsp->msg = 'Playlist is complete';
                    $ps->setStatus(self::STATUS_COMPLETE);
                    $ps->setParameters($plsp);
                    $ps->save();
                    return $plsp;
                }
                $plsp->current_videos_id_index = 0;
            }
        }
        $plsp->modifiedTime = time();
        if (!empty($plsp->videos_id_history) && empty($plsp->current_videos_id_index)) {
            $plsp->loop_count++;
        }
        $plsp->current_videos_id = $videosArrayId[$plsp->current_videos_id_index];
        $plsp->videos_id_history[] = $plsp->current_videos_id;
        if (count($plsp->videos_id_history) > $plsp->totalVideos * 2) {
            $plsp->videos_id_history = array_slice($plsp->videos_id_history, -$plsp->totalVideos);
        }
        $plsp->msg = "Playing " . ($plsp->current_videos_id_index + 1) . "/{$plsp->totalVideos} loop {$plsp->loop_count}";
        $plsp->play = true;

        $ps->setStatus(self::STATUS_EXECUTING);
        $ps->setParameters($plsp);
        $ps->save();

        return $plsp;
    }
}

class PlaylistsSchedulesParameters
{
    public $current_videos_id_index = 0;
    public $playlists_schedules_id = 0;
    public $playlists_id = 0;
    public $msg = '';
    public $videos_id_history = array();
    public $current_videos_id = 0;
    public $startTime = 0;
    public $modifiedTime = 0;
    public $totalVideos = 0;
    public $play = false;
    public $fromParameters = false;
}
