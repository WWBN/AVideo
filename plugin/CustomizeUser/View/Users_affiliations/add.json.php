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

$o = new Users_affiliations(@$_POST['id']);

if(!User::isAdmin()){
    if(User::isACompany()){
        $_POST['users_id_company'] = User::getId();
        $_POST['company_agree_date'] = date('Y-m-d H:i:s');
        _error_log('Users_affiliations: save is a company');
    }else{
        $_POST['users_id_affiliate'] = User::getId();
        $_POST['affiliate_agree_date'] = date('Y-m-d H:i:s');
        _error_log('Users_affiliations: save is NOT a company');
    }
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
