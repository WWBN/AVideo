<?php
require_once $global['systemRootPath'] . 'objects/userGroups.php';
$userGroups = UserGroups::getAllUsersGroups();
?>
<style>
    .usergroupsLi .dynamicLabel{
        display: none;
    }
    .usergroupsLi.dynamic .dynamicLabel{
        display: inline;
    }
    .usergroupsLi.dynamic .label-warning{
        background-color: #999;
    }
</style>