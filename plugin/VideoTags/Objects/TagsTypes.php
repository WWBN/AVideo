<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';
require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/TagsHasVideos.php';

class TagsTypes extends ObjectYPT {

    protected $id, $name, $parameters_json;

    static function getSearchFieldsNames() {
        return array('name');
    }

    static function getTableName() {
        return 'tags_types';
    }
    
    function getName() {
        return $this->name;
    }

    function getParameters_json() {
        return $this->parameters_json;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setParameters_json($parameters_json) {
        $this->parameters_json = $parameters_json;
    }


    
        
}
