<?php

//streamer config
require_once dirname(__FILE__) . '/../../videos/configuration.php';

if(!AVideoPlugin::isEnabledByName('Scheduler')){
    forbiddenPage('Scheduler is disabled');
}

if(empty($_REQUEST['title'])){
    forbiddenPage('Title cannot be empty');
}
if(empty($_REQUEST['date_start'])){
    forbiddenPage('date_start cannot be empty');
}

Scheduler::downloadICS($_REQUEST['title'], $_REQUEST['date_start'], @$_REQUEST['date_end'], @$_REQUEST['reminder'], @$_REQUEST['joinURL'], @$_REQUEST['description']);