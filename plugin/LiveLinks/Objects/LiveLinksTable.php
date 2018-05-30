<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class LiveLinksTable extends ObjectYPT {

    protected $id, $title, $description, $link, $start_date, $end_date, $type, $status, $users_id;

    static function getSearchFieldsNames() {
        return array('title', 'description');
    }

    static function getTableName() {
        return 'LiveLinks';
    }
    
   function save() {
       if(!User::isLogged()){
           return false;
       }
       
       if(empty($this->users_id)){
           $this->users_id = User::getId();
       }
       return parent::save();
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

   function getLink() {
       return $this->link;
   }

   function getStart_date() {
       return $this->start_date;
   }

   function getEnd_date() {
       return $this->end_date;
   }

   function getType() {
       return $this->type;
   }

   function getStatus() {
       return $this->status;
   }

   function getUsers_id() {
       return $this->users_id;
   }

   function setTitle($title) {
       $this->title = $title;
   }

   function setDescription($description) {
       $this->description = $description;
   }

   function setLink($link) {
       $this->link = $link;
   }

   function setStart_date($start_date) {
       $this->start_date = $start_date;
   }

   function setEnd_date($end_date) {
       $this->end_date = $end_date;
   }

   function setType($type) {
       $this->type = $type;
   }

   function setStatus($status) {
       $this->status = $status;
   }

   function setUsers_id($users_id) {
       $this->users_id = $users_id;
   }



}
