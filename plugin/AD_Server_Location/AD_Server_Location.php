<?php
/**
 * https://support.google.com/adsense/answer/4455881
 * https://support.google.com/adsense/answer/1705822
 * AdSense for video: Publisher Approval Form
 * https://services.google.com/fb/forms/afvapproval/
 */
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server_Location/Objects/CampaignLocations.php';
require_once $global['systemRootPath'] . 'plugin/User_Location/Objects/IP2Location.php';

class AD_Server_Location extends PluginAbstract {

    public function getDescription() {
        $desc = "Enable select location to display each ad<br>";
        
        $desc .= $this->isReadyLabel(array('AD_Server'));
        
        return $desc;
    }

    public function getName() {
        return "AD_Server_Location";
    }

    public function getUUID() {
        return "77771553-61a2-4189-b033-27a6bb17173d";
    }

    public function getPluginVersion() {
        return "1.0";   
    }    

    public function getEmptyDataObject() {
        $obj = new stdClass();
        return $obj;
    }
    
    public function getCampaignPanel(){
        global $global;
        include $global['systemRootPath'] . 'plugin/AD_Server_Location/campaignPanel.php';
    }
    
    public function addCampaignLocation($country_name, $region_name, $city_name, $vast_campaigns_id){
        if(!is_array($country_name)){
            $country_name = array($country_name);
        }
        if(!is_array($region_name)){
            $region_name = array($region_name);
        }
        if(!is_array($city_name)){
            $city_name = array($city_name);
        }
        foreach ($country_name as $key => $value) {
            $cl = new CampaignLocations(0);
            $cl->setCountry_name($country_name[$key]);
            $cl->setRegion_name($region_name[$key]);
            $cl->setCity_name($city_name[$key]);
            $cl->setVast_campaigns_id($vast_campaigns_id);
            $cl->save();
        }
    }
    
    public function getCampaignLocations($vast_campaigns_id){
        $cl = new CampaignLocations(0);
        $cl->setVast_campaigns_id($vast_campaigns_id);
        return $cl->getCampaignLocations();
    }
    

}
