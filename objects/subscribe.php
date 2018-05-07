<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';

class Subscribe {

    private $id;
    private $email;
    private $status;
    private $ip;
    private $users_id;
    private $notify;

    function __construct($id, $email = "", $user_id = "") {
        if (!empty($id)) {
            $this->load($id);
        }
        if (!empty($email)) {
            $this->email = $email;
            $this->users_id = $user_id;
            if (empty($this->id)) {
                $this->loadFromEmail($email, $user_id, "");
            }
        }
    }

    private function load($id) {
        $obj = self::getSubscribe($id);
        if (empty($obj))
            return false;
        foreach ($obj as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    private function loadFromEmail($email, $user_id, $status = "a") {
        $obj = self::getSubscribeFromEmail($email, $user_id, $status);
        if (empty($obj))
            return false;
        foreach ($obj as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    function save() {
        global $global;
        if (!empty($this->id)) {
            $sql = "UPDATE subscribes SET status = '{$this->status}',  notify = '{$this->notify}',ip = '" . getRealIpAddr() . "', modified = now() WHERE id = {$this->id}";
        } else {
            $sql = "INSERT INTO subscribes ( users_id, email,status,ip, created, modified) VALUES ('{$this->users_id}','{$this->email}', 'a', '" . getRealIpAddr() . "',now(), now())";
        }
        $resp = $global['mysqli']->query($sql);
        if (empty($resp)) {
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $resp;
    }

    static function getSubscribe($id) {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM subscribes WHERE  id = $id LIMIT 1";
        $res = $global['mysqli']->query($sql);
        if ($res) {
            $subscribe = $res->fetch_assoc();
        } else {
            $subscribe = false;
        }
        return $subscribe;
    }

    static function getSubscribeFromEmail($email, $user_id, $status = "a") {
        global $global;
        $sql = "SELECT * FROM subscribes WHERE  email = '$email' AND users_id = {$user_id} ";
        if (!empty($status)) {
            $sql .= " AND status = '{$status}' ";
        }
        $sql .= " LIMIT 1";
        $res = $global['mysqli']->query($sql);
        if ($res) {
            $subscribe = $res->fetch_assoc();
        } else {
            $subscribe = false;
        }
        return $subscribe;
    }

    /**
     * return all subscribers that has subscribe to an user channel
     * @global type $global
     * @param type $user_id
     * @return boolean
     */
    static function getAllSubscribes($user_id = "") {
        global $global;
        $sql = "SELECT su.id as subscriber_id, s.* FROM subscribes as s "
                . " LEFT JOIN users as su ON s.email = su.email   "
                . " LEFT JOIN users as u ON users_id = u.id  WHERE 1=1 ";
        if (!empty($user_id)) {
            $sql .= " AND users_id = {$user_id} ";
        }
        $sql .= BootGrid::getSqlFromPost(array('email'));

        $res = $global['mysqli']->query($sql);
        $subscribe = array();
        if ($res) {
            $emails = array();
            while ($row = $res->fetch_assoc()) {
                if(in_array($row['email'], $emails)){
                    continue;
                }
                $emails[] = $row['email'];
                $row['identification'] = User::getNameIdentificationById($row['subscriber_id']);
                if($row['identification'] === __("Unknown User")){
                    $row['identification'] = $row['email'];
                }
                $row['backgroundURL'] = User::getBackground($row['subscriber_id']);
                $row['photoURL'] = User::getPhoto($row['subscriber_id']);
                
                $subscribe[] = $row;
            }
            //$subscribe = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $subscribe = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $subscribe;
    }
    
    /**
     * return all channels that a user has subscribed
     * @global type $global
     * @param type $user_id
     * @return boolean
     */
    static function getSubscribedChannels($user_id) {
        global $global;
        
        $user = new User($user_id);
        $email = $user->getEmail();
        
        $sql = "SELECT s.* FROM subscribes as s WHERE email='{$email}' ";
        
        $res = $global['mysqli']->query($sql);
        $subscribe = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['identification'] = User::getNameIdentificationById($row['users_id']);
                if($row['identification'] === __("Unknown User")){
                    $row['identification'] = $row['email'];
                }
                $row['backgroundURL'] = User::getBackground($row['users_id']);
                $row['photoURL'] = User::getPhoto($row['users_id']);
                
                $subscribe[] = $row;
            }
            //$subscribe = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $subscribe = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $subscribe;
    }

    static function getTotalSubscribes($user_id = "") {
        global $global;
        $sql = "SELECT id FROM subscribes WHERE status = 'a' ";
        if (!empty($user_id)) {
            $sql .= " AND users_id = {$user_id} ";
        }

        $sql .= BootGrid::getSqlSearchFromPost(array('email'));

        $res = $global['mysqli']->query($sql);


        return $res->num_rows;
    }

    function toggle() {
        if (empty($this->status) || $this->status == "i") {
            $this->status = 'a';
        } else {
            $this->status = 'i';
        }
        $this->save();
    }
    
    function notifyToggle() {
        if (empty($this->notify)) {
            $this->notify = 1;
        } else {
            $this->notify = 0;
        }
        $this->save();
    }

    function getStatus() {
        return $this->status;
    }
    
    function getNotify() {
        return $this->notify;
    }

    function setNotify($notify) {
        $this->notify = $notify;
    }

    
    static function getButton($user_id) {
        $total = static::getTotalSubscribes($user_id);
        
        $subscribe = "<div class=\"btn-group\">"
                . "<button class='btn btn-xs subsB subs{$user_id} subscribeButton{$user_id}'><i class='fab fa-youtube'></i> <b class='text'>" . __("Subscribe") . "</b></button>"
                . "<button class='btn btn-xs subsB subs{$user_id}'><b class='textTotal{$user_id}'>{$total}</b></button>"
                . "</div>";
                
        //show subscribe button with mail field
        $popover = "<div id=\"popover-content\" class=\"hide\">
        <div class=\"input-group\"  style=\"max-height:34px;\">
          <input type=\"text\" placeholder=\"E-mail\" class=\"form-control\"  id=\"subscribeEmail{$user_id}\" style=\"min-width: 150px;\">
          <span class=\"input-group-btn\">
          <button class=\"btn btn-danger\" id=\"subscribeButton{$user_id}2\"><i class=\"fa fa-check\"></i></button>
          </span>
        </div>
    </div><script>
$(document).ready(function () {
$(\".subscribeButton{$user_id}\").popover({
placement: 'bottom',
trigger: 'manual',
    html: true,
	content: function() {
          return $('#popover-content').html();
        }
});
});
</script>";
        $script = "<script>
            function toogleNotify{$user_id}(){
                email = $('#subscribeEmail{$user_id}').val();
                if (validateEmail(email)) {
                    subscribeNotify(email, {$user_id});
                }
            }
            $(document).ready(function () {
                $(\".subscribeButton{$user_id}\").off(\"click\");
                $(\".subscribeButton{$user_id}\").click(function () {
                    email = $('#subscribeEmail{$user_id}').val();
                    if (validateEmail(email)) {
                        subscribe(email, {$user_id});
                    }
                });
                $('[data-toggle=\"tooltip\"]').tooltip(); 
            });
        </script>";
        if (User::isLogged()) {
            //check if the email is logged
            $email = User::getMail();
            if (!empty($email)) {
                $subs = Subscribe::getSubscribeFromEmail($email, $user_id);
                $popover = "<input type=\"hidden\" placeholder=\"E-mail\" class=\"form-control\"  id=\"subscribeEmail{$user_id}\" value=\"{$email}\">";
                if (!empty($subs)) {
                    // show unsubscribe Button
                    $subscribe = "<div class=\"btn-group\">"
                . "<button class='btn btn-xs subsB subscribeButton{$user_id} subscribed subs{$user_id}'><i class='fab fa-youtube'></i> <b class='text'>" . __("Subscribed") . "</b></button>"
                . "<button class='btn btn-xs subsB subscribed subs{$user_id}'><b class='textTotal{$user_id}'>$total</b></button>"
                . "</div>";
        
                    if(!empty($subs['notify'])){
                        $notify = '';
                        $notNotify = 'hidden';
                    }else{
                        $notify = 'hidden';
                        $notNotify = '';
                    }
                    $subscribe .= '<span class=" notify'.$user_id.' '.$notify.'"><button onclick="toogleNotify'.$user_id.'();" class="btn btn-default btn-xs " data-toggle="tooltip" 
                                   title="'.__("Stop getting notified for every new video").'">
                                <i class="fa fa-bell" ></i>
                            </button></span><span class=" notNotify'.$user_id.' '.$notNotify.'"><button onclick="toogleNotify'.$user_id.'();" class="btn btn-default btn-xs "  data-toggle="tooltip" 
                                   title="'.__("Get notified for every new video").'">
                                <i class="fa fa-bell-slash"></i>
                            </button></span>';
                }
            }
        }
        return $subscribe.$popover.$script;
    }

}
