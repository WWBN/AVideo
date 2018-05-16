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
            $format = "sssiisi";
            $values = array($this->name,$this->clean_name,$this->description,$this->nextVideoOrder,$this->parentId,$this->getIconClass(),$this->id);
        } else {
            $sql = "INSERT INTO categories ( name,clean_name,description,nextVideoOrder,parentId,iconClass, created, modified) VALUES ('{$this->name}', '{$this->clean_name}','{$this->description}','{$this->nextVideoOrder}','{$this->parentId}', '{$this->getIconClass()}',now(), now())";
            $format = "sssiis";
            $values = array($this->name,$this->clean_name,$this->description,$this->nextVideoOrder,$this->parentId,$this->getIconClass());
        }
        
        if (!sqlDAL::writeSql($sql,$format,$values)) {
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error." SQL-CMD: ".$sql);
        }
        return true;
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
            $sql = "DELETE FROM categories WHERE id = ?";
        } else {
            return false;
        }
        if (!sqlDAL::writeSql($sql,"i",array($this->id))) {
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error." SQL-CMD: ".$sql);
        }
        return true;
    }

    static function getCategoryType($categoryId){
        global $global;
        $sql = "SELECT * FROM `category_type_cache` WHERE categoryId = ?;";
        $res = sqlDAL::readSql($sql,"i",array($categoryId));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
	    if($res) {
	       if(!empty($data)){
               return $data;
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
        $sql = "SELECT * FROM categories WHERE id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql,"i",array($id)); 
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return ($res) ? $result : false;
    }
    
    static function getCategoryByName($name) {
        global $global;
        $sql = "SELECT * FROM categories WHERE clean_name = ? LIMIT 1";
        $res = sqlDAL::readSql($sql,"s",array($name)); 
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return ($res) ? $result : false;
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
        $res = sqlDAL::readSql($sql); 
        $fullResult = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $category = array();
        if ($res) {
            foreach ($fullResult as $row) {
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
        $sql = "SELECT * FROM categories WHERE parentId=? AND id!=? ";         
        
        $sql .= BootGrid::getSqlFromPost(array('name'), "", " ORDER BY name ASC ");
   
        $res = sqlDAL::readSql($sql,"ii",array($parentId,$parentId),true); 
        $fullResult = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        
        //$res = $global['mysqli']->query($sql);
        $category = array();
        if ($res) {
            foreach ($fullResult as $row) {
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
        $res = sqlDAL::readSql($sql);
        $numRows = sqlDal::num_rows($res);
        sqlDAL::close($res);
        return $numRows;
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
