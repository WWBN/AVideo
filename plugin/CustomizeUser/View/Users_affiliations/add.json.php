<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Users_affiliations.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('CustomizeUser');

if(!User::isLogged()){
    forbiddenPage();
}

// editing an existing row requires being one of its two parties (or admin), same gate as confirm.json.php/delete.json.php
if (!empty($_POST['id']) && !Users_affiliations::canEditAffiliation($_POST['id'])) {
    forbiddenPage();
}

$o = new Users_affiliations(@$_POST['id']);

if(!User::isAdmin()){
    // Existing consent belongs to the stored party, even if the account's company status has changed.
    $isCompany = empty($o->getId()) ? User::isACompany() : $o->getUsers_id_company() == User::getId();
    if($isCompany){
        $_POST['users_id_company'] = User::getId();
        $_POST['company_agree_date'] = date('Y-m-d H:i:s');
        $_POST['affiliate_agree_date'] = $o->getAffiliate_agree_date(); // only the affiliate may set this, via confirm.json.php
        _error_log('Users_affiliations: save is a company');
    }else{
        $_POST['users_id_affiliate'] = User::getId();
        $_POST['affiliate_agree_date'] = date('Y-m-d H:i:s');
        $_POST['company_agree_date'] = $o->getCompany_agree_date(); // only the company may set this, via confirm.json.php
        _error_log('Users_affiliations: save is NOT a company');
    }
    if (!empty($o->getId())) {
        // an edit keeps both stored parties: replacing the other party kept that party's agree date, so the new
        // party became affiliated without consenting (GHSA-695j-7p82-jvh2). A new party needs a new invitation.
        $_POST['users_id_company'] = $o->getUsers_id_company();
        $_POST['users_id_affiliate'] = $o->getUsers_id_affiliate();
    }
    unset($_POST['status']); // status is derived in Users_affiliations::save(), never caller-supplied
}
_error_log('Users_affiliations: save '. _json_encode($_POST));

$o->setUsers_id_company($_POST['users_id_company']);
$o->setUsers_id_affiliate($_POST['users_id_affiliate']);
$o->setStatus(@$_POST['status']);
$o->setCompany_agree_date(@$_POST['company_agree_date']);
$o->setAffiliate_agree_date(@$_POST['affiliate_agree_date']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
