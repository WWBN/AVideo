<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class CombineSitesGive extends ObjectYPT {

    protected $id, $combine_sites_id, $users_id, $categories_id, $playlists_id, $status;

    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'combine_sites_give_elements';
    }

    function getCombine_sites_id() {
        return $this->combine_sites_id;
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function getCategories_id() {
        return $this->categories_id;
    }

    function getPlaylists_id() {
        return $this->playlists_id;
    }

    function getStatus() {
        return $this->status;
    }

    function setCombine_sites_id($combine_sites_id) {
        $this->combine_sites_id = $combine_sites_id;
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    function setCategories_id($categories_id) {
        $this->categories_id = $categories_id;
    }

    function setPlaylists_id($playlists_id) {
        $this->playlists_id = $playlists_id;
    }

    function setStatus($status) {
        $this->status = $status;
    }
    
    public function save() {
        if(empty($this->users_id)){
            $this->users_id = "NULL";
        }
        if(empty($this->categories_id)){
            $this->categories_id = "NULL";
        }
        if(empty($this->playlists_id)){
            $this->playlists_id = "NULL";
        }
        return parent::save();
    }

    static function addChannel($combine_sites_id, $users_id, $status) {
        $c = self::getChannel($combine_sites_id, $users_id);
        if($c){
            $o = new CombineSitesGive($c['id']);
        }else{
            $o = new CombineSitesGive();
            $o->setCombine_sites_id($combine_sites_id);
            $o->setUsers_id($users_id);
        }
        $o->setStatus($status);
        return $o->save();
        
    }
    
    static function getChannel($combine_sites_id, $users_id) {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  combine_sites_id = ? AND users_id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "ii", array($combine_sites_id, $users_id));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
        
    }

    static function addCategories($combine_sites_id, $categories_id, $status) {
        $c = self::getCategories($combine_sites_id, $categories_id);
        if($c){
            $o = new CombineSitesGive($c['id']);
        }else{
            $o = new CombineSitesGive();
            $o->setCombine_sites_id($combine_sites_id);
            $o->setCategories_id($categories_id);
        }
        $o->setStatus($status);
        return $o->save();
        
    }
    
    static function getCategories($combine_sites_id, $categories_id) {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  combine_sites_id = ? AND categories_id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "ii", array($combine_sites_id, $categories_id));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
        
    }

    static function addPrograms($combine_sites_id, $playlists_id, $status) {
        $c = self::getPrograms($combine_sites_id, $playlists_id);
        if($c){
            $o = new CombineSitesGive($c['id']);
        }else{
            $o = new CombineSitesGive();
            $o->setCombine_sites_id($combine_sites_id);
            $o->setPlaylists_id($playlists_id);
        }
        $o->setStatus($status);
        return $o->save();
        
    }
    
    static function getPrograms($combine_sites_id, $playlists_id) {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  combine_sites_id = ? AND playlists_id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "ii", array($combine_sites_id, $playlists_id));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
        
    }
    
    /**
     * 
     * @global type $global
     * @param type $combine_sites_id
     * @param type $activeOnly
     * @param type $type can be users_id, categories_id, playlists_id
     * @return boolean
     */
    static function getAllFromSite($combine_sites_id, $activeOnly = false, $type = false) {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $combine_sites_id = intval($combine_sites_id);
        
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE combine_sites_id = {$combine_sites_id} ";

        if($activeOnly){
            $sql .= " AND status = 'a' ";
        }
        
        if(!empty($type)){
            $type = strtolower($type);
            if($type == 'users_id'){
                $sql .= " AND (users_id IS NOT NULL AND users_id != '') ";
            }else if($type == 'categories_id'){
                $sql .= " AND (categories_id IS NOT NULL AND categories_id != '') ";
            }else if($type == 'playlists_id'){
                $sql .= " AND (playlists_id IS NOT NULL AND playlists_id != '') ";
            }
        }
        
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

}
