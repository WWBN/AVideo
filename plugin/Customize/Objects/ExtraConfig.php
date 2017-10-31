<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class ExtraConfig extends Object {

    protected $id, $about, $description, $footer;
    
    function __construct() {
        $this->id = 1;
        parent::__construct(1);
    }

    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'extraConfig';
    }
    
    function getAbout() {
        return $this->about;
    }

    function getDescription() {
        return $this->description;
    }

    function getFooter() {
        return $this->footer;
    }

    function setAbout($about) {
        $this->about = $about;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setFooter($footer) {
        $this->footer = $footer;
    }    
}
