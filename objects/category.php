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

    function setName($name) {
        $this->name = $name;
    }

    function setClean_name($clean_name) {
        preg_replace('/\W+/', '-', strtolower(cleanString($clean_name)));
        $this->clean_name = $clean_name;
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
            $sql = "UPDATE categories SET name = '{$this->name}',clean_name = '{$this->clean_name}',description = '{$this->description}',iconClass = '{$this->getIconClass()}', modified = now() WHERE id = {$this->id}";
        } else {
            $sql = "INSERT INTO categories ( name,clean_name,description,iconClass, created, modified) VALUES ('{$this->name}', '{$this->clean_name}','{$this->description}', '{$this->getIconClass()}',now(), now())";
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

    static function getCategory($id) {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM categories WHERE  id = $id LIMIT 1";
        $res = $global['mysqli']->query($sql);
        return ($res) ? $res->fetch_assoc() : false;
    }

    static function getAllCategories() {
        global $global;
        $sql = "SELECT * FROM categories WHERE 1=1 ";         
        
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
        $sql = "SELECT id FROM categories WHERE 1=1  ";
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
