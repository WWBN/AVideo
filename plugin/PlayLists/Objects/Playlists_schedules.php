<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Playlists_schedules extends ObjectYPT
{

    const STATUS_ACTIVE = 'a';
    const STATUS_INACTIVE = 'i';
    const STATUS_EXECUTING = 'e';
    const STATUS_EXECUTED = 'x';
    const STATUS_COMPLETE = 'c';
    const STATUS_FAIL = 'f';

    const STATUS_TEXT = array(
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_EXECUTING => 'Executing',
        self::STATUS_EXECUTED => 'Executed',
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
        return intval($this->start_datetime);
    }

    function getFinish_datetime()
    {
        return intval($this->finish_datetime);
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

    public static function getAllExecuted()
    {
        return self::getAllFromStatus(self::STATUS_EXECUTED);
    }

    public static function getAllExecuting()
    {
        return self::getAllFromStatus(self::STATUS_EXECUTING);
    }

    public function save()
    {
        if(empty($this->playlists_id)){
            _error_log("Playlists_schedules::save playlists_id is empty");
            return false;
        }
        if (empty($this->status)) {
            $this->status = self::STATUS_ACTIVE;
        }
        _error_log("start={$this->start_datetime}  finish={$this->finish_datetime}");
        return parent::save();
    }

    static function getCleanVideosIDArray($playlists_id, $depth=0){
        $cleanVideosArrayId = array();
        $videosArrayId = PlayList::getVideosIdFromPlaylist($playlists_id);
        foreach ($videosArrayId as $key => $value) {
            $video = new Video('', '', $value);
            if ($video->getType() == Video::$videoTypeVideo) {
                $cleanVideosArrayId[] = $value;
            }else if ($video->getType() == Video::$videoTypeSerie && !empty($video->getSerie_playlists_id()) && $depth < 4){
                $newVideosArrayId = self::getCleanVideosIDArray($video->getSerie_playlists_id(), $depth+1);
                $cleanVideosArrayId = array_merge($cleanVideosArrayId, $newVideosArrayId);
            }
        }
        return $cleanVideosArrayId;
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

        $cleanVideosArrayId = self::getCleanVideosIDArray($plsp->playlists_id);

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
        if(empty($plsp->loop_count)){
            $plsp->loop_count = 0;
        }
        $plsp->modifiedTime = time();
        if (!empty($plsp->videos_id_history) && empty($plsp->current_videos_id_index)) {
            $plsp->loop_count++;
        }
        $plsp->current_videos_id = $cleanVideosArrayId[$plsp->current_videos_id_index];
        $plsp->videos_id_history[] = $plsp->current_videos_id;
        if (count($plsp->videos_id_history) > $plsp->totalVideos * 2) {
            $plsp->videos_id_history = array_slice($plsp->videos_id_history, -$plsp->totalVideos);
        }
        $plsp->msg = "Playing " . ($plsp->current_videos_id_index + 1) . "/{$plsp->totalVideos} loop {$plsp->loop_count}";
        $plsp->play = true;

        if($ps->getFinish_datetime() < time()){
            $ps->setStatus(self::STATUS_COMPLETE);
        }else{
            $ps->setStatus(self::STATUS_EXECUTING);
        }

        $ps->setParameters($plsp);
        $ps->save();

        return $plsp;
    }

    static function getDynamicTitle($playlists_schedules_id)
    {
        $ps = new Playlists_schedules($playlists_schedules_id);
        $title = $ps->getName();
        $parametersText = $ps->getParameters();
        if (!empty($parametersText)) {
            $plsp = _json_decode($parametersText);
            $video = new Video('', '', $plsp->current_videos_id);
            $title .= ' ['.$video->getTitle().']';
            //$title .= __('Playing now')." {$plsp->current_videos_id_index}/{$plsp->totalVideos} loop {$plsp->loop_count}";
        }
        return $title;
    }

    static function getDynamicDescription($playlists_schedules_id)
    {
        $ps = new Playlists_schedules($playlists_schedules_id);
        $description = '';
        $parametersText = $ps->getParameters();
        if (!empty($parametersText)) {
            $plsp = _json_decode($parametersText);
            if(empty($plsp->current_videos_id)){
                return '';
            }
            $video = new Video('', '', $plsp->current_videos_id);
            if(empty($video)){
                return '';
            }
            $description .= '<strong>'.$video->getTitle().'</strong><hr>';
            $description .= '<small class="text-muted">'.__('Playing now').' '.($plsp->current_videos_id_index+1)."/{$plsp->totalVideos} loop {$plsp->loop_count}</small>";
            $url = PlayLists::getLink($ps->getPlaylists_id());

            $description .= '<a href="'.$url.'" class="btn btn-default pull-right" target="_top"><i class="fa-solid fa-up-right-from-square"></i></a>';
        }
        if(User::isAdmin() || $video->getUsers_id() == User::getId()){
            $description .= PlayLists::scheduleLiveButton($ps->getPlaylists_id(), true, 'btn btn-default btn-block');
        }
        return $description;
    }

    static function getPlayListScheduledIndex($playlists_schedules){
        return "ps-{$playlists_schedules}";
    }
    
    function canRecordVideo($key) {
        return empty(Playlists_schedules::iskeyPlayListScheduled($key));
    }

    static function iskeyPlayListScheduled($key){
        if(preg_match('/([0-9a-z]+)-ps-([0-9]+)/', $key, $matches)){
            if(!empty($matches[2])){
                return array('key'=>$key, 'cleankey'=>$matches[1], 'playlists_schedules'=>$matches[2]);
            }
        }
        return false;
    }

    static public function stopBroadcast($playlists_schedules_id){
        $ps = new Playlists_schedules($playlists_schedules_id);
        $ps->setStatus(self::STATUS_COMPLETE);
        return $ps->save();
    }

    
    public static function getAll($playlists_id = 0)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";
        $formats = '';
        $values = [];
        if(!empty($playlists_id)){
            $sql .= " AND playlists_id = ? ";
            $formats .= 'i';
            $values[] = $playlists_id;
        }
        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql, $formats, $values);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res !== false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        }
        return $rows;
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
