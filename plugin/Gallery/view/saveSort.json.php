<?php

require_once '../../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->sectionsSaved = array();
$obj->namesSaved = array();

if(!User::isAdmin()){
    $obj->msg = "Must be admin";
    die(json_encode($obj));
}

// CSRF protection: this endpoint mutates plugin configuration.
// SameSite=None on session cookies (required for cross-origin iframe embeds)
// means a cross-site POST will carry the admin session; a token is required.
if (!isGlobalTokenValid()) {
    http_response_code(403);
    $obj->msg = "Invalid or missing CSRF token";
    die(json_encode($obj));
}

$gallery = AVideoPlugin::loadPlugin('Gallery', true);

if(!empty($_REQUEST['sections'])){
    $object = $gallery->getDataObject();
    foreach ($_REQUEST['sections'] as $key => $value) {
        // Security: section names must be alphanumeric/underscore only (e.g. 'Shorts',
        // 'Channel_123_'). Reject anything else to prevent code injection via eval()
        // in the old implementation and to guard setDataObject() property names.
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
            continue;
        }
        $obj->sectionsSaved[] = array($key => $value);
        // Use safe dynamic property access instead of eval()
        $property = $value . 'Order';
        $object->$property = intval($key);
    }
    $obj->error = !$gallery->setDataObject($object);
}else if(!empty ($_REQUEST['name'])){
    // Security: validate name before passing to setDataObjectParameter() which
    // internally uses eval(); an unsanitized name would allow code injection.
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $_REQUEST['name'])) {
        $obj->msg = "Invalid section name";
        die(json_encode($obj));
    }
    $isChecked = true;
    if(empty($_REQUEST['isChecked']) || $_REQUEST['isChecked'] == 'false'){
        $isChecked = false;
    }
    $obj->namesSaved[] = array($_REQUEST['name']=>$isChecked);
    $gallery->setDataObjectParameter($_REQUEST['name'], $isChecked);
}

$obj->error = false;
die(json_encode($obj));
