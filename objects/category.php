<?php

require_once dirname(__FILE__) . '/../videos/configuration.php';
require_once dirname(__FILE__) . '/../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../objects/user.php';

class Category {

    private $id;
    private $name;
    private $clean_name;
    private $description;
    private $iconClass;
    private $nextVideoOrder;
    private $parentId;
    private $type;

    function setName($name) {
        $this->name = $name;
    }

    function setClean_name($clean_name) {
        preg_replace('/\W+/', '-', strtolower(cleanString($clean_name)));
        $this->clean_name = $clean_name;
    }

    function setNextVideoOrder($nextVideoOrder) {
        $this->nextVideoOrder = $nextVideoOrder;
    }

    function setParentId($parentId) {
        $this->parentId = $parentId;
    }
   
    function setType($type){
        global $global;
        $exist = false;
        // require this cause of Video::autosetCategoryType - but should be moveable easy here..
        require_once dirname(__FILE__) . '/../objects/video.php';
        $sql = "SELECT * FROM `category_type_cache` WHERE categoryId = ?";
        $res = sqlDAL::readSql($sql,"i",array($this->id));
        $catTypeCache = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if($catTypeCache!=false){
            $exist = true;
        }

        if($type=="3"){ 
            // auto-cat-type
            if($exist){
                $sql = "UPDATE `category_type_cache` SET `manualSet` = '0' WHERE `category_type_cache`.`categoryId` = ?";
            } else {
                $sql = "INSERT INTO `category_type_cache` (`categoryId`, `type`, `manualSet`) VALUES (?, '0','0')";
            }
            sqlDAL::writeSql($sql,"i",array($this->id));
            
            //$res = $global['mysqli']->query($sql);
            Video::autosetCategoryType($this->id);
        } else {
            if($exist){
                $sql = "UPDATE `category_type_cache` SET `type` = ?, `manualSet` = '1' WHERE `category_type_cache`.`categoryId` = ?;";
                sqlDAL::writeSql($sql,"si",array($type,$this->id));

            } else {
                $sql = "INSERT INTO `category_type_cache` (`categoryId`, `type`, `manualSet`) VALUES (?,?,'1')";
                sqlDAL::writeSql($sql,"is",array($this->id,$type));
            }
        }
    }
    
    function setDescription($description) {
        $this->description = $description;
    }
    
    function __construct($id, $name = '') {
        if (empty($id)) {
            // get the category data from category and pass
            $this->name = $name;
        } else {
            // get data from id
            $this->load($id);
        }
    }

    private function load($id) {
        $category = self::getCategory($id);
        $this->id = $category['id'];
        $this->name = $category['name'];
    }

    function loadSelfCategory() {
        $this->load($this->getId());
    }

    function save() {
        global $global;
        if (empty($this->isAdmin)) {
            $this->isAdmin = "false";
        }
        if (!empty($this->id)) {
            $sql = "UPDATE categories SET name = '{$this->name}',clean_name = '{$this->clean_name}',description = '{$this->description}',nextVideoOrder = '{$this->nextVideoOrder}',parentId = '{$this->parentId}',iconClass = '{$this->getIconClass()}', modified = now() WHERE id = {$this->id}";
        } else {
            $sql = "INSERT INTO categories ( name,clean_name,description,nextVideoOrder,parentId,iconClass, created, modified) VALUES ('{$this->name}', '{$this->clean_name}','{$this->description}','{$this->nextVideoOrder}','{$this->parentId}', '{$this->getIconClass()}',now(), now())";
        }
        $resp = $global['mysqli']->query($sql);
        if (empty($resp)) {
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $resp;
    }

    function delete() {
        if (!User::isAdmin()) {
            return false;
        }
        // cannot delete default category
        if ($this->id == 1) {
            return false;
        }

        global $global;
        if (!empty($this->id)) {
            $sql = "DELETE FROM categories WHERE id = {$this->id}";
        } else {
            return false;
        }
        $resp = $global['mysqli']->query($sql);
        if (empty($resp)) {
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $resp;
    }

    static function getCategoryType($categoryId){
        global $global;
        $sql = "SELECT * FROM `category_type_cache` WHERE categoryId = '".$categoryId."';";
        $res = $global['mysqli']->query($sql);
	if($res) {
	$sres = $res->fetch_assoc();
	if(!empty($sres)){
        	return $sres;
	} else {
		return array("categoryId" => "-1","type"=>"0","manualSet" => "0");
		}
	}
	else {
		return array("categoryId" => "-1","type"=>"0","manualSet" => "0");
	}
    }
    static function getCategory($id) {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM categories WHERE id = $id LIMIT 1";
        $res = $global['mysqli']->query($sql);
        return ($res) ? $res->fetch_assoc() : false;
    }
    
    static function getCategoryByName($name) {
        global $global;
        $sql = "SELECT * FROM categories WHERE clean_name = '$name' LIMIT 1";
        $res = $global['mysqli']->query($sql);
        return ($res) ? $res->fetch_assoc() : false;
    }

    static function getAllCategories() {
        global $global, $config;
        if($config->currentVersionLowerThen('5.01')){
            return false;
        }
        $sql = "SELECT * FROM categories WHERE 1=1 ";
        if(!empty($_GET['parentsOnly'])){
            $sql .= "AND parentId = 0 ";
        }
        $sql .= BootGrid::getSqlFromPost(array('name'), "", " ORDER BY name ASC ");
        
        $res = $global['mysqli']->query($sql);
        $category = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $category[] = $row;
            }
            //$category = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $category = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $category;
    }
    
    static function getChildCategories($parentId) {
        global $global, $config;
        if($config->currentVersionLowerThen('5.01')){
            return false;
        }
        $sql = "SELECT * FROM categories WHERE parentId=".$parentId." AND id!=".$parentId." ";         
        
        $sql .= BootGrid::getSqlFromPost(array('name'), "", " ORDER BY name ASC ");
        
        $res = $global['mysqli']->query($sql);
        $category = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $category[] = $row;
            }
            //$category = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $category = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $category;
    }
    
    

    static function getTotalCategories() {
        global $global;
        $sql = "SELECT id, parentId FROM categories WHERE 1=1 ";
        if(!empty($_GET['parentsOnly'])){
            $sql .= "AND parentId = 0 OR parentId = -1 ";
        }
        $sql .= BootGrid::getSqlSearchFromPost(array('name'));

        $res = $global['mysqli']->query($sql);

        return $res->num_rows;
    }

    function getIconClass() {
        if (empty($this->iconClass)) {
            return "fa fa-folder";
        }
        return $this->iconClass;
    }

    function setIconClass($iconClass) {
        $this->iconClass = $iconClass;
    }

}
