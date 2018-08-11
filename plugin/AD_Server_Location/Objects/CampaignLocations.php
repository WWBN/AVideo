<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class CampaignLocations extends ObjectYPT {

    protected $id, $country_name, $region_name, $city_name, $vast_campaigns_id;

    static function getSearchFieldsNames() {
        return array('country_name','region_name','city_name');
    }

    static function getTableName() {
        return 'campaign_locations';
    }
    
    function getCountry_name() {
        return $this->country_name;
    }

    function getRegion_name() {
        return $this->region_name;
    }

    function getCity_name() {
        return $this->city_name;
    }

    function getVast_campaigns_id() {
        return $this->vast_campaigns_id;
    }

    function setCountry_name($country_name) {
        global $global;
        $country_name = $global['mysqli']->real_escape_string($country_name);
        $this->country_name = $country_name;
    }

    function setRegion_name($region_name) {
        global $global;
        $region_name = $global['mysqli']->real_escape_string($region_name);
        $this->region_name = $region_name;
    }

    function setCity_name($city_name) {
        global $global;
        $city_name = $global['mysqli']->real_escape_string($city_name);
        $this->city_name = $city_name;
    }

    function setVast_campaigns_id($vast_campaigns_id) {
        $this->vast_campaigns_id = $vast_campaigns_id;
    }
    
    public function getCampaignLocations(){
        global $global;
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE vast_campaigns_id={$this->vast_campaigns_id} ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql); 
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res!=false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    
    static function deleteFromCapmpaign($vast_campaigns_id) {
        global $global;
        if (!empty($vast_campaigns_id)) {
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE vast_campaigns_id = ?";
            $global['lastQuery'] = $sql;
            //error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql,"i",array($vast_campaigns_id));
        }
        error_log("Id for table " . static::getTableName() . " not defined for deletion");
        return false;
    }

}
