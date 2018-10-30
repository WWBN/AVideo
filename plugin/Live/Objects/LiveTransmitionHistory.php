<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class LiveTransmitionHistory extends ObjectYPT {

    protected $id, $title, $description, $key, $created, $modified, $users_id;

    static function getSearchFieldsNames() {
        return array('title', 'description');
    }

    static function getTableName() {
        return 'live_transmitions_history';
    }

    function getId() {
        return $this->id;
    }

    function getTitle() {
        return $this->title;
    }

    function getDescription() {
        return $this->description;
    }

    function getKey() {
        return $this->key;
    }

    function getCreated() {
        return $this->created;
    }

    function getModified() {
        return $this->modified;
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setTitle($title) {
        $this->title = $title;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setKey($key) {
        $this->key = $key;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function setModified($modified) {
        $this->modified = $modified;
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }


    
}
