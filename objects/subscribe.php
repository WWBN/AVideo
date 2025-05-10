<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';

class Subscribe extends ObjectYPT{
    protected $properties = [];
    protected $id;
    protected $email;
    protected $status;
    protected $ip;
    protected $users_id;
    protected $notify;
    protected $subscriber_users_id;

    /**
     * Undocumented function
     *
     * @param int $id
     * @param string $email
     * @param int $user_id the channel owner
     * @param int $subscriber_users_id the user that will subscribe
     */
    public function __construct($id, $email = "", $user_id = "", $subscriber_users_id = "")
    {
        if (!empty($id)) {
            $this->load($id);
        }
        if (!empty($subscriber_users_id)) {
            $this->email = $email;
            $this->users_id = $user_id;
            $this->subscriber_users_id = $subscriber_users_id;
            if (empty($this->id)) {
                $this->loadFromId($this->subscriber_users_id, $user_id, "");
            }
        }
    }

    public function load($id, $refreshCache = false)
    {
        $obj = self::getSubscribe($id);
        if (empty($obj)) {
            return false;
        }
        foreach ($obj as $key => $value) {
            @$this->$key = $value;
            //$this->properties[$key] = $value;
        }
        return true;
    }

    protected function loadFromEmail($email, $user_id, $status = "a")
    {
        $obj = self::getSubscribeFromEmail($email, $user_id, $status);
        if (empty($obj)) {
            return false;
        }
        foreach ($obj as $key => $value) {
            @$this->$key = $value;
            //$this->properties[$key] = $value;
        }
        return true;
    }

    protected function loadFromId($subscriber_users_id, $user_id, $status = "a")
    {
        $obj = self::getSubscribeFromID($subscriber_users_id, $user_id, $status);
        if (empty($obj)) {
            return false;
        }
        foreach ($obj as $key => $value) {
            @$this->$key = $value;
            //$this->properties[$key] = $value;
        }
        return true;
    }

    public function save()
    {
        global $global;
        if (!empty($this->id)) {
            $sql = "UPDATE subscribes SET status = '{$this->status}',  notify = '{$this->notify}',ip = '" . getRealIpAddr() . "', modified = now() WHERE id = {$this->id}";
        } else {
            $this->status = 'a';
            $sql = "INSERT INTO subscribes ( users_id, email,status,ip, created, modified, subscriber_users_id) VALUES ('{$this->users_id}','{$this->email}', '{$this->status}', '" . getRealIpAddr() . "',now(), now(), '$this->subscriber_users_id')";
        }
        $saved = sqlDAL::writeSql($sql);
        if($saved){
            //var_dump($saved, $this->status);exit;
            if($this->status == 'a'){
                AVideoPlugin::onNewSubscription($this->users_id, $this->subscriber_users_id);
            }
        }

        return $saved;
    }

    public static function getSubscribe($id)
    {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM subscribes WHERE  id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", [$id]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res !== false) {
            $subscribe = $data;
        } else {
            $subscribe = false;
        }
        return $subscribe;
    }

    public static function getSubscribeFromEmail($email, $user_id, $status = "a")
    {
        global $global;
        $status = str_replace("'", "", $status);
        $sql = "SELECT * FROM subscribes WHERE  email = '$email' AND users_id = {$user_id} ";
        if (!empty($status)) {
            $sql .= " AND status = '{$status}' ";
        }
        $sql .= " LIMIT 1";
        $res = sqlDAL::readSql($sql);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res !== false) {
            $subscribe = $data;
        } else {
            $subscribe = false;
        }
        return $subscribe;
    }

    public static function getSubscribeFromID($subscriber_users_id, $user_id, $status = "a"){
        $subscriber_users_id = intval($subscriber_users_id);
        $user_id = intval($user_id);
        if(empty($user_id) || empty($subscriber_users_id)){
            return false;
        }
        global $global;
        $status = str_replace("'", "", $status);
        $sql = "SELECT * FROM subscribes WHERE  subscriber_users_id = '$subscriber_users_id' AND users_id = {$user_id} ";
        if (!empty($status)) {
            $sql .= " AND status = '{$status}' ";
        }
        $sql .= " LIMIT 1";
        $res = sqlDAL::readSql($sql, "", []);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res !== false) {
            $subscribe = $data;
        } else {
            $subscribe = false;
        }
        return $subscribe;
    }

    public static function isSubscribed($subscribed_to_user_id, $user_id = 0)
    {
        if (empty($user_id)) {
            if (User::isLogged()) {
                $user_id = User::getId();
            } else {
                return false;
            }
        }
        $s = self::getSubscribeFromID($subscribed_to_user_id, $user_id);
        return !empty($s['users_id']);
    }

    /**
     * return all subscribers that has subscribe to an user channel
     * @global array $global
     * @param string $user_id
     * @return array
     */
    public static function getAllSubscribes($user_id = "", $status = "a", $verifiedOnly = false)
    {
        global $global;
        $cacheName = "getAllSubscribes_{$user_id}_{$status}_" . getCurrentPage() . "_" . getRowCount();
        $subscribe = ObjectYPT::getCache($cacheName, 300); // 5 minutes
        if (empty($subscribe)) {
            $status = str_replace("'", "", $status);
            $sql = "SELECT subscriber_users_id as subscriber_id, s.id, s.status, s.ip, s.users_id, s.notify, "
                    . " s.subscriber_users_id , s.created , s.modified, suId.email as email, suId.emailVerified as emailVerified FROM subscribes as s "
                    //. " LEFT JOIN users as su ON s.email = su.email   "
                    . " LEFT JOIN users as suId ON suId.id = s.subscriber_users_id   "
                    . " LEFT JOIN users as u ON users_id = u.id  WHERE 1=1 AND subscriber_users_id > 0 ";
            if (!empty($user_id)) {
                $sql .= " AND users_id = {$user_id} ";
            }
            if (!empty($status)) {
                $sql .= " AND u.status = '{$status}' ";
                $sql .= " AND suId.status = '{$status}' ";
                //$sql .= " AND su.status = '{$status}' ";
            }
            if (!empty($verifiedOnly)) {
                $sql .= " AND suId.emailVerified = 1 ";
            }

            //$sql .= " GROUP BY subscriber_id ";

            $sql .= BootGrid::getSqlFromPost(['email']);


            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $subscribe = [];
            if ($res !== false) {
                $emails = [];
                foreach ($fullData as $row) {
                    $row = cleanUpRowFromDatabase($row);
                    if (in_array($row['email'], $emails)) {
                        //continue;
                    }
                    //$value['notify'] =
                    $emails[] = $row['email'];
                    $row['identification'] = User::getNameIdentificationById($row['subscriber_id']);
                    if ($row['identification'] === __("Unknown User")) {
                        $row['identification'] = $row['email'];
                    }
                    $row['backgroundURL'] = User::getBackground($row['subscriber_id']);
                    $row['photoURL'] = User::getPhoto($row['subscriber_id']);


                    $row['channel_identification'] = User::getNameIdentificationById($row['users_id']);
                    $row['channel_backgroundURL'] = User::getBackground($row['users_id']);
                    $row['channel_photoURL'] = User::getPhoto($row['users_id']);

                    $subscribe[] = $row;
                }
                //$subscribe = $res->fetch_all(MYSQLI_ASSOC);
                ObjectYPT::setCache($cacheName, $subscribe);
            } else {
                //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        } else {
            $subscribe = object_to_array($subscribe);
        }
        return $subscribe;
    }

    /**
     * return all channels that a user has subscribed
     * @global array $global
     * @param string $user_id
     * @return array
     */
    public static function getSubscribedChannels($user_id, $limit = 0, $page = 0)
    {
        global $global;
        $limit = intval($limit);
        $page = intval($page) - 1;
        if ($page < 0) {
            $page = 0;
        }
        $offset = $limit * $page;
        $sql = "SELECT s.*, (SELECT MAX(v.created) FROM videos v WHERE v.users_id = s.users_id) as newestvideo "
                . " FROM subscribes as s WHERE status = 'a' AND subscriber_users_id = ? "
                . " ORDER BY newestvideo DESC ";

        if (!empty($limit)) {
            $sql .= " LIMIT {$offset},{$limit} ";
        }
        //var_dump($sql, $user_id);exit;
        $res = sqlDAL::readSql($sql, "i", [$user_id]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $subscribe = [];
        if ($res !== false) {
            foreach ($fullData as $row) {
                $row['identification'] = User::getNameIdentificationById($row['users_id']);
                if ($row['identification'] === __("Unknown User")) {
                    $row['identification'] = $row['email'];
                }
                $row['channelName'] = User::_getChannelName($row['users_id']);
                $row['backgroundURL'] = User::getBackground($row['users_id']);
                $row['photoURL'] = User::getPhoto($row['users_id']);

                $current = getCurrentPage();
                $rowCount = getRowCount();
                $sort = @$_POST['sort'];

                $_POST['current'] = 1;
                $_REQUEST['rowCount'] = 6;
                $_POST['sort']['created'] = "DESC";
                $row['latestVideos'] = Video::getAllVideos(Video::SORT_TYPE_VIEWABLE, $row['users_id']);
                foreach ($row['latestVideos'] as $key => $video) {
                    $images = Video::getImageFromFilename($video['filename'], $video['type']);
                    $row['latestVideos'][$key]['Thumbnail'] = $images->thumbsJpg;
                    $row['latestVideos'][$key]['createdHumanTiming'] = humanTiming(strtotime($video['created']));
                    $row['latestVideos'][$key]['pageUrl'] = Video::getLink($video['id'], $video['clean_title'], false);
                    $row['latestVideos'][$key]['embedUrl'] = Video::getLink($video['id'], $video['clean_title'], true);
                    $row['latestVideos'][$key]['UserPhoto'] = User::getPhoto($video['users_id']);
                }
                $_POST['current'] = $current;
                $_REQUEST['rowCount'] = $rowCount;
                $_POST['sort'] = $sort;

                $row['totalViewsIn30Days'] = VideoStatistic::getChannelsTotalViews($row['users_id']);

                $subscribe[] = $row;
            }
            //$subscribe = $res->fetch_all(MYSQLI_ASSOC);
        }
        return $subscribe;
    }

    public static function getTotalSubscribes($user_id = 0)
    {
        global $global;
        $sql = "SELECT id FROM subscribes WHERE status = 'a' AND users_id > 0 ";
        if (!empty($user_id)) {
            $sql .= " AND users_id = '{$user_id}' ";
        }

        //$sql .= BootGrid::getSqlSearchFromPost(['email']);
        //echo $sql, '<br>', PHP_EOL;
        $res = sqlDAL::readSql($sql);
        $numRows = sqlDAL::num_rows($res);
        sqlDAL::close($res);

        $extra = User::getExtraSubscribers($user_id);
        return $numRows+$extra;
    }

    public static function getTotalSubscribedChannels($user_id = "")
    {
        global $global;
        $sql = "SELECT id FROM subscribes WHERE status = 'a' AND subscriber_users_id = ? ";

        //$sql .= BootGrid::getSqlSearchFromPost(array('email'));
        $res = sqlDAL::readSql($sql, "i", [$user_id]);
        $numRows = sqlDAL::num_rows($res);
        sqlDAL::close($res);


        return $numRows;
    }

    public function toggle()
    {
        if (empty($this->status) || $this->status == "i") {
            $this->status = 'a';
        } else {
            $this->status = 'i';
        }
        $this->save();
    }

    public function notifyToggle()
    {
        if (empty($this->notify)) {
            $this->notify = 1;
        } else {
            $this->notify = 0;
        }
        $this->save();
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getNotify(){
        if(!isset($this->notify)){
            return 1;
        }
        return $this->notify;
    }

    public function setNotify($notify)
    {
        $this->notify = $notify;
    }

    public static function getButton($user_id)
    {
        global $global, $advancedCustom;

        if (!empty($advancedCustom->removeSubscribeButton)) {
            return "";
        }
        $rowCount = getRowCount();
        $total = static::getTotalSubscribes($user_id);
        $btnFile = $global['systemRootPath'] . 'view/subscribeBtnOffline.html';

        $notify = '';
        $email = '';
        $subscribed = '';
        $subscribeText = __("Subscribe");
        $subscribedText = __("Subscribed");
        if (User::isLogged()) {
            $btnFile = $global['systemRootPath'] . 'view/subscribeBtn.html';
            $email = User::getMail();
            $subs = Subscribe::getSubscribeFromID(User::getId(), $user_id);
            if (!empty($subs['notify'])) {
                $notify = 'notify';
            }

            if (!empty($subs) && $subs['status'] === 'a') {
                $subscribed = 'subscribed';
            }
        }
        $content = local_get_contents($btnFile);

        $signInBTN = ("<a class='btn btn-primary btn-sm btn-block' href='{$global['webSiteRootURL']}user'>".__("Sign in to subscribe to this channel")."</a>");

        $search = [
            '_user_id_',
            '{notify}',
            '{tooltipStop}',
            '{tooltip}',
            '{titleOffline}',
            '{tooltipOffline}',
            '{email}', '{total}',
            '{subscribed}', '{subscribeText}', '{subscribedText}'
        ];

        $replace = [
            $user_id,
            $notify,
            __("Stop getting notified for every new video"),
            __("Click to get notified for every new video"),
            __("Want to subscribe to this channel?"),
            $signInBTN,
            $email, $total,
            $subscribed, $subscribeText, $subscribedText, ];

        $btnHTML = str_replace($search, $replace, $content);
        return $btnHTML;
    }

    public function getSubscriber_users_id()
    {
        return $this->subscriber_users_id;
    }

    public function setSubscriber_users_id($subscriber_users_id)
    {
        $this->subscriber_users_id = $subscriber_users_id;
    }

    public function getUsers_id()
    {
        return $this->users_id;
    }

    public function setUsers_id($users_id)
    {
        $this->users_id = $users_id;
    }

    public static function getTableName() {
        return 'subscribes';
    }

}
