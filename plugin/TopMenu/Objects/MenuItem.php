<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class MenuItem extends ObjectYPT {

    protected $id, $title, $image, $url, $class, $style, $item_order, $topMenu_id, $status, $text, $icon, $clean_url, $menuSeoUrlItem;

    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'topMenu_items';
    }
    
    static function getAllFromMenu($menu_id, $activeOnly = false, $sort = true) {
        global $global;
        $menu_id = intval($menu_id);
        if(empty($menu_id)){
            return false;
        }
        $sql = "SELECT * FROM  ".static::getTableName()." WHERE topMenu_id = {$menu_id}";
        
        if($activeOnly){
            $sql .= " AND status = 'active' ";
        }       

        if($sort){
            $sql .= " ORDER BY item_order ";
        }
        
        $res = $global['mysqli']->query($sql);
        $rows = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    
    function setTitle($title) {
        $this->title = $title;
    }

    function setImage($image) {
        $this->image = $image;
    }

    function setUrl($url) {
        $this->url = $url;
    }

    function setClass($class) {
        $this->class = $class;
    }

    function setStyle($style) {
        $this->style = $style;
    }

    function setItem_order($item_order) {
        $this->item_order = intval($item_order);
    }

    function setTopMenu_id($topMenu_id) {
        $this->topMenu_id = intval($topMenu_id);
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setText($text) {
        $this->text = $text;
    }    
    
    function setIcon($icon) {
        $this->icon = $icon;
    }

    function setClean_url($clean_url) {
        $this->clean_url = $clean_url;
    }
    
    function setmenuSeoUrlItem($menuSeoUrlItem){
        $this->menuSeoUrlItem=$menuSeoUrlItem;
    }

        
    function save() {
        global $global;
        if(empty($this->title)){
            $this->title = "Unknow Item Menu Title";
        }
        if(empty($this->status)){
            $this->status = "active";
        }
        if(empty($this->menuSeoUrlItem)){
            $this->menuSeoUrlItem=$this->title;
        }
        $this->menuSeoUrlItem=$global['mysqli']->real_escape_string(preg_replace('/[^a-z0-9]+/', '_', strtolower($this->title)));     
        
        $this->title = $global['mysqli']->real_escape_string($this->title);
        $this->text = $global['mysqli']->real_escape_string($this->text);
        
        return parent::save();
    }
    
    function getTitle() {
        return $this->title;
    }

    function getText() {
        return $this->text;
    }
    
    function getUrl() {
        return $this->url;
    }




    
}
