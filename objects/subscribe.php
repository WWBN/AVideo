<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';

class Subscribe {

    private $id;
    private $email;
    private $status;
    private $ip;


    function __construct($id, $email) {
        if (!empty($id)){
            $this->load($id);
        }
        if(!empty($email)){
            $this->email = $email;
            if(empty($this->id)){
                $this->loadFromEmail($email, "");
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
    
    
    private function loadFromEmail($email, $status = "a") {
        $obj = self::getSubscribeFromEmail($email, $status);
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
            $sql = "UPDATE subscribes SET email = '{$this->email}',status = '{$this->status}',ip = '".getRealIpAddr()."', modified = now() WHERE id = {$this->id}";
        } else {
            $sql = "INSERT INTO subscribes ( email,status,ip, created, modified) VALUES ('{$this->email}', 'a', '".getRealIpAddr()."',now(), now())";
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

    static function getSubscribeFromEmail($email, $status = "a") {
        global $global;
        $sql = "SELECT * FROM subscribes WHERE  email = '$email' ";
        if(!empty($status)){
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

    static function getAllSubscribes() {
        global $global;
        $sql = "SELECT * FROM subscribes WHERE 1=1 ";

        $sql .= BootGrid::getSqlFromPost(array('email'));

        $res = $global['mysqli']->query($sql);
        $subscribe = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $subscribe[] = $row;
            }
            //$subscribe = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $subscribe = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $subscribe;
    }

    static function getTotalSubscribes() {
        global $global;
        $sql = "SELECT id FROM subscribes WHERE 1=1  ";

        $sql .= BootGrid::getSqlSearchFromPost(array('email'));

        $res = $global['mysqli']->query($sql);


        return $res->num_rows;
    }
    
    function toggle(){
        if(empty($this->status) || $this->status == "i"){
            $this->status = 'a';
        }else{
            $this->status = 'i';
        }
        $this->save();
    }
    
    function getStatus() {
        return $this->status;
    }



}
