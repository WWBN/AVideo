<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/UserConnections/Objects/Users_connections.php';

class UserConnections extends PluginAbstract
{

    public function getDescription()
    {
        $desc = "Allows users to connect, send friend requests, and manage relationships on your platform. Integrated with Chat2, Meet, and Socket plugins, it enables real-time messaging, calls, and notifications, enhancing user interaction and community building";
        //$desc .= $this->isReadyLabel(array('YPTWallet'));
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/UserConnections-Plugin' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $desc . $help;
    }

    public function getName()
    {
        return "UserConnections";
    }

    public function getUUID()
    {
        return "UserConnections-5ee8405eaaa16";
    }

    public function getPluginVersion()
    {
        return "1.0";
    }

    public function updateScript()
    {
        global $global;
        /*
        if (AVideoPlugin::compareVersion($this->getName(), "2.0") < 0) {
            sqlDal::executeFile($global['systemRootPath'] . 'plugin/PayPerView/install/updateV2.0.sql');
        }
         * 
         */
        return true;
    }

    public function getEmptyDataObject()
    {
        $obj = new stdClass();
        /*
        $obj->textSample = "text";
        $obj->checkboxSample = true;
        $obj->numberSample = 5;
        
        $o = new stdClass();
        $o->type = array(0=>__("Default"))+array(1,2,3);
        $o->value = 0;
        $obj->selectBoxSample = $o;
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->textareaSample = $o;
        */
        return $obj;
    }


    public function getPluginMenu()
    {
        global $global;
        return '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/UserConnections/View/editor.php\')" class="btn btn-primary btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';
    }

    static function connectUsers($users_id1, $users_id2)
    {
        $row = Users_connections::getConnection($users_id1, $users_id2);
        if (empty($row)) {
            $o = new Users_connections(0);
            $o->setUsers_id1($users_id1);
            $o->setUsers_id2($users_id2);
            $o->save();
            $row = Users_connections::getConnection($users_id1, $users_id2);
        }
        return $row;
    }

    static function connectMe($users_id)
    {
        $my_users_id = User::getId();

        if (empty($my_users_id)) {
            _error_log("connectMe($users_id) you must login first");
            return false;
        }
        if ($users_id == $my_users_id) {
            _error_log("connectMe($users_id) you cannot connect with yourself");
            return false;
        }
        $users_id2 = $users_id;
        $row = self::connectUsers($my_users_id, $users_id2);
        $o = new Users_connections($row['id']);
        if ($my_users_id == $row['users_id1']) {
            $o->setUser1_status(Users_connections::STATUS_APPROVED);
            self::newFriendNotification($row['users_id1'], $row['users_id2']);
        } else if ($my_users_id == $row['users_id2']) {
            $o->setUser2_status(Users_connections::STATUS_APPROVED);
            self::newFriendConfirmNotification($row['users_id2'], $row['users_id1']);
        }
        return $o->save();
    }

    static function disconnectMe($users_id)
    {
        $my_users_id = User::getId();

        if ($users_id == $my_users_id) {
            return false;
        }
        if (empty($my_users_id)) {
            return false;
        }
        $users_id2 = $users_id;
        $row = self::connectUsers($my_users_id, $users_id2);
        $o = new Users_connections($row['id']);

        return $o->delete();
    }

    static function isConnectionValid($users_id1, $users_id2)
    {
        $row = Users_connections::getConnection($users_id1, $users_id2);
        if (empty($row)) {
            return false;
        }
        return $row['user1_status'] === Users_connections::STATUS_APPROVED && $row['user2_status'] === Users_connections::STATUS_APPROVED;
    }

    static function getMyConnectionStatus($users_id)
    {
        $users_id1 = User::getId();
        $users_id2 = $users_id;
        $row = Users_connections::getConnection($users_id1, $users_id2);

        return self::getMyConnectionStatusFromRow($users_id1, $row);
    }

    static function getMyConnectionStatusFromRow($my_users_id, $row)
    {
        if (!empty($row)) {
            if ($row['users_id1'] == $my_users_id) {
                return array('mine' => $row['user1_status'], 'friend' => $row['user2_status']);
            }
            if ($row['users_id2'] == $my_users_id) {
                return array('mine' => $row['user2_status'], 'friend' => $row['user1_status']);
            }
        }
        return array('mine' => Users_connections::STATUS_INACTIVE, 'friend' => Users_connections::STATUS_INACTIVE);
    }

    static function getCurrentConnectionStatus($my_users_id, $row)
    {
        $status = self::getMyConnectionStatusFromRow($my_users_id, $row);
        if ($status['mine'] == Users_connections::STATUS_APPROVED) {
            if ($status['friend'] != Users_connections::STATUS_APPROVED) {
                return Users_connections::STATUS_PENDING;
            } else {
                return Users_connections::STATUS_APPROVED;
            }
        } else {
            return Users_connections::STATUS_I_NEED_TO_APPROVE;
        }
    }


    static function getAllMyConnections($validOnly = false)
    {
        return Users_connections::getAllConnections(User::getId(), $validOnly);
    }

    function getChannelPageButtons($users_id)
    {
        return self::connectButton($users_id);
    }

    public static function connectButton($users_id)
    {

        global $global, $config;

        $filePath = $global['systemRootPath'] . 'plugin/UserConnections/connectButton.php';
        $varsArray = array('users_id' => $users_id);
        $button = getIncludeFileContent($filePath, $varsArray);

        return $button;
    }

    public static function profileTabName($users_id)
    {
        $p = AVideoPlugin::loadPlugin("UserConnections");
        $obj = $p->getDataObject();
        return '<li><a data-toggle="tab" href="#proftab' . $p->getUUID() . '"><i class="fa-solid fa-user-friends"></i> ' . __('My Friends') . '</a></li>';
    }

    public static function profileTabContent($users_id)
    {
        global $global;
        $p = AVideoPlugin::loadPlugin("UserConnections");
        $obj = $p->getDataObject();
        $tabId = 'proftab' . $p->getUUID();
        include $global['systemRootPath'] . 'plugin/UserConnections/View/profileTabContent.php';
        return "";
    }

    public function getFooterCode()
    {        
        global $global;

        $obj = $this->getDataObject();
        $content = '<script src="' . getURL('plugin/UserConnections/script.js') . '"></script>';
        
        $file = $global['systemRootPath'] . 'plugin/UserConnections/View/menu.php';
        $content .= getIncludeFileContent($file);

        return $content;
    }

    public function getHeadCode()
    {
        return '<link href="' . getURL('plugin/UserConnections/style.css') . '" rel="stylesheet" type="text/css" />';
    }

    public static function getConnectionButtons($users_id)
    {
        if (!User::isLogged()) {
            return "<!-- getConnectionButtons($users_id) not logged -->";
        }
        if ($users_id == User::getId()) {
            return "<!-- getConnectionButtons($users_id) this is your users IO -->";
        }

        $status = UserConnections::getMyConnectionStatus($users_id);
        $html = '';

        $html .= '<div class="userConnectButtons' . $users_id . ' connectionStatus connectionStatus_' . $status['mine'] . $status['friend'] . '">';

        // When the User is Not a Friend
        $html .= '<button class="btn btn-primary btn-xs connectMe showOnConnectionStatus_i" onclick="connectMe(' . $users_id . ');" data-toggle="tooltip" title="' . __("Send Friend Request") . '">';
        $html .= '<i class="fas fa-user-plus"></i> <small>' . __('Send Friend Request') . '</small>';
        $html .= '</button>';

        // When the Friend Request is Pending Approval
        $html .= '<button class="btn btn-warning btn-xs pendingApproval showOnConnectionStatus_n" onclick="connectMe(' . $users_id . ');" data-toggle="tooltip" title="' . __("Friend Request Pending Your Approval") . '">';
        $html .= '<i class="fas fa-hourglass-half"></i> <small>' . __('Friend Request Pending Your Approval') . '</small>';
        $html .= '</button>';

        // When the Friend Request is Waiting for Approval
        $html .= '<button class="btn btn-info btn-xs requestedApproval showOnConnectionStatus_p" onclick="disconnectMe(' . $users_id . ');" data-toggle="tooltip" title="' . __("Friend Request Sent, Awaiting Approval") . '">';
        $html .= '<i class="fas fa-user-clock"></i> <small>' . __('Friend Request Sent, Awaiting Approval') . '</small>';
        $html .= '</button>';

        // When the User is Already a Friend
        $html .= '<button class="btn btn-success btn-xs disconnectMe showOnConnectionStatus_a" onclick="disconnectMe(' . $users_id . ');" data-toggle="tooltip" title="' . __("Friend Added") . '">';
        $html .= '<i class="fas fa-user-check"></i> <small>' . __('Friend Added') . '</small>';
        $html .= '</button>';

        $html .= '</div>';

        return $html;
    }

    public static function newFriendNotification($my_users_id, $friend_users_id)
    {
        global $global;
        $title = 'New Friend Request Received';
        $identification = User::getNameIdentificationById($my_users_id);
        $msg = 'You have received a new friend request from [' . $identification . ']';
        $element_id = "newFriendNotification";
        $icon = 'fas fa-user-friends';
        $type = UserNotifications::type_info;
        return self::createNotification($my_users_id, $friend_users_id, $title, $msg, $type, $element_id, $icon);
    }

    public static function newFriendConfirmNotification($my_users_id, $friend_users_id)
    {
        global $global;
        $title = 'Friend Request Accepted';
        $identification = User::getNameIdentificationById($my_users_id);
        $msg = '[' . $identification . '] has accepted your friend request. You are now connected!';
        $element_id = "newFriendConfirmNotification";
        $icon = 'fas fa-user-check';
        $type = UserNotifications::type_success;
        return self::createNotification($my_users_id, $friend_users_id, $title, $msg, $type, $element_id, $icon);
    }

    public static function createNotification($my_users_id, $friend_users_id, $title, $msg, $type, $element_id, $icon)
    {
        global $global;
        $image = User::getPhoto($my_users_id, false, true);
        $href = User::getChannelLink($my_users_id);
        $element_id = "{$element_id}_{$my_users_id}_{$friend_users_id}";
        //sendSocketSuccessMessageToUsers_id($msg, $friend_users_id);
        return UserNotifications::createNotification($title, $msg, $friend_users_id, $image, $href, $type, $element_id, $icon);
    }
    
}
