<?php
if(empty($global['systemRootPath'])){
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'].'objects/bootGrid.php';
class User{
    private $id;
    private $user;
    private $name;
    private $email;
    private $password;
    private $isAdmin;
    
    function __construct($id, $user="", $password="") {
        if(empty($id)){
            // get the user data from user and pass
            $this->user = $user;
            $this->password = $password;
        }else{
            // get data from id
            $this->load($id);
        }
        
    }
    
    function getEmail() {
        return $this->email;
    }
    
    function getUser() {
        return $this->user;
    }
    
    private function load($id){
        $user = $this->getUserDb($id);
        $this->id = $user['id'];
        $this->user = $user['user'];
        $this->name = $user['name'];
        $this->email = $user['email'];
        $this->password = $user['password'];
        $this->isAdmin = $user['isAdmin'];
    }

    function loadSelfUser(){
        $this->load($this->getId());
    }

    static function getId() {
        if(self::isLogged()){
            return $_SESSION['user']['id'];
        }else{
            return false;
        }
    }

    static function getName() {
        if(self::isLogged()){
            return $_SESSION['user']['name'];
        }else{
            return false;
        }
    }

    function save(){
        global $global;
        if(empty($this->user) || empty($this->password)){
            die('Error : ' . __("You need a user and passsword to register"));
        }
        if(empty($this->isAdmin)){
            $this->isAdmin = "false";
        }
        if(!empty($this->id)){
            $sql = "UPDATE users SET user = '{$this->user}', password = '{$this->password}', email = '{$this->email}', name = '{$this->name}', isAdmin = {$this->isAdmin} , modified = now() WHERE id = {$this->id}";
        }else{
            $sql = "INSERT INTO users (user, password, email, name, isAdmin, created, modified) VALUES ('{$this->user}','{$this->password}','{$this->email}','{$this->name}',{$this->isAdmin}, now(), now())";            
        }
        $resp = $global['mysqli']->query($sql);
        if(empty($resp)){
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $resp;
    }
    
    
    function delete(){
        if(!self::isAdmin()){
            return false;
        }
        // cannot delete yourself
        if(self::getId() === $this->id){
            return false;
        }
        
        global $global;
        if(!empty($this->id)){
            $sql = "DELETE FROM users WHERE id = {$this->id}";
        }else{
            return false;
        }
        $resp = $global['mysqli']->query($sql);
        if(empty($resp)){
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $resp;
    }
    
    function login(){
        $user = $this->find($this->user, $this->password);
        if($user){
            $_SESSION['user'] = $user;
        }else{
            unset($_SESSION['user']);
        }
    }
        
    static function logoff(){
        unset($_SESSION['user']);
    }
    
    static function isLogged(){
        return !empty($_SESSION['user']);
    }
    
    static function isAdmin(){
        return !empty($_SESSION['user']['isAdmin']);
    }
    
    private function find($user, $pass){
        global $global;
        
        $user = $global['mysqli']->real_escape_string($user);
        $pass = md5($pass);
        $sql = "SELECT * FROM users WHERE user = '$user' AND password = '$pass' LIMIT 1";
        $res = $global['mysqli']->query($sql);
        if($res){
            $user = $res->fetch_assoc();
        }else{
            $user = false;
        }
        return $user;
    }

    private function getUserDb($id) {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM users WHERE  id = $id LIMIT 1";
        $res = $global['mysqli']->query($sql);
        if ($res) {
            $user = $res->fetch_assoc();
        } else {
            $user = false;
        }
        return $user;
    }
    
    function setUser($user) {
        $this->user = $user;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setPassword($password) {
        if(!empty($password)){
            $this->password = md5($password);
        }
    }

    function setIsAdmin($isAdmin) {
        if(empty($isAdmin) || $isAdmin === "false"){
            $isAdmin ="false";
        }else{
            $isAdmin = "true";
        }
        $this->isAdmin = $isAdmin;
    }

    static function getAllUsers(){
        if(!self::isAdmin()){
            return false;
        }
        //will receive 
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        $sql = "SELECT * FROM users WHERE 1=1 ";
        
        $sql .= BootGrid::getSqlFromPost(array('name', 'email', 'user'));
        
        $res = $global['mysqli']->query($sql);
        
        if ($res) {
            $user = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $user = false;
            die($sql.'\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $user;
    }
    
    static function getTotalUsers(){
        if(!self::isAdmin()){
            return false;
        }
        //will receive 
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        $sql = "SELECT id FROM users WHERE 1=1  ";
        
        $sql .= BootGrid::getSqlSearchFromPost(array('name', 'email', 'user'));
        
        $res = $global['mysqli']->query($sql);
        
        
        return $res->num_rows;
    }

}