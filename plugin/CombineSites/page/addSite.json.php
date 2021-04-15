<?php

error_reporting(0);
global $global, $config;

require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CombineSites/Objects/CombineSitesDB.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->response = new stdClass();

if (empty($_REQUEST['site_url']) || !filter_var($_REQUEST['site_url'], FILTER_VALIDATE_URL)) {
    $obj->msg = "Invalid site_url ({$_REQUEST['site_url']})";
    _dieAndLogObject($obj, "CombineSites::addsite: ");
}
//checking if the last character is a '/' if not then tack it on
$_REQUEST['site_url'] = rtrim($_REQUEST['site_url'], '/') . '/';

if($global['webSiteRootURL'] == $_REQUEST['site_url']){
    $obj->error = false;
    $obj->msg = "We cannot add our own site";
    _dieAndLogObject($obj, "CombineSites::addsite: ");
}

$o = new CombineSitesDB(0);
$o->loadFromSite($_REQUEST['site_url']);

if ($o->getId()) {
    _error_log("CombineSites::addsite: we found this site ". json_encode($_REQUEST));
    if (!empty($_REQUEST['give_token']) && $_REQUEST['give_token'] === $o->getGive_token()) {
        // we recognize this site
        if (!empty($_REQUEST['get_token'])) {
            $o->setGet_token($_REQUEST['get_token']);
            if ($o->save()) {
                $obj->error = false;
                $obj->msg = "get_token for site ({$_REQUEST['site_url']}) updated";
                _dieAndLogObject($obj, "CombineSites::addsite: ");
            }
        }else{
            _error_log("CombineSites::addsite: get_token is empty ");
        }
    }else{
        _error_log("CombineSites::addsite: give_token not found or does not match ({$_REQUEST['give_token']}) == ".$o->getGive_token());
    }

    if ($o->getStatus() !== 'a') {
        $obj->msg = "Site ({$_REQUEST['site_url']}) is not approved";
        _dieAndLogObject($obj, "CombineSites::addsite: ");
    }
    $obj->error = false;
    $obj->msg = "Site ({$_REQUEST['site_url']}) already exists";
    _dieAndLogObject($obj, "CombineSites::addsite: ");
}

$o->setSite_url($_REQUEST['site_url']);
$o->setStatus('i');
$token = uniqid();
$o->setGive_token($token);

if (!empty($_REQUEST['get_token'])) {
    $o->setGet_token($_REQUEST['get_token']);
} else {
    $_REQUEST['get_token'] = "";
}

if ($o->save()) {
    $obj->error = false;
    // request access to my site
    $url = "{$_REQUEST['site_url']}plugin/CombineSites/page/addSite.json.php?site_url=".urlencode($global['webSiteRootURL'])."&get_token={$token}&give_token={$_REQUEST['get_token']}";
    _error_log("CombineSites::addsite: requesting {$url} ");
    $obj->response = _json_decode(url_get_contents($url));
}
_dieAndLogObject($obj, "CombineSites::addsite: ");
