<?php
//streamer config
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

ob_end_flush();

$sql = "select * FROM users";
$res = sqlDAL::readSql($sql);
$fullData = sqlDAL::fetchAllAssoc($res);
sqlDAL::close($res);
$rows = [];
$count = ['NoPermanent'=>0, 'NoDynamic'=>0, 'Deleted'=>0];
if ($res != false) {
    foreach ($fullData as $key => $row) {
        $rowsUser = UserGroups::getUserGroups($row['id']);
        if (empty($rowsUser)) {
            //echo "This user has no permanent usergroups".PHP_EOL;
            $count['NoPermanent']++;
        } else {
            $user = new User($row['id'], $row['user'], $row['password']);
            $user->login(true, false, true);
            $user_groups_id = AVideoPlugin::getDynamicUserGroupsId($row['id']);
            if (empty($user_groups_id)) {
                //echo "This user has no dynamic usergroups".PHP_EOL;
                $count['NoDynamic']++;
            } else {
                echo "found dynamic usergroups from users_id={$row['id']} user={$row['user']} usergroups=". implode(',', $user_groups_id).PHP_EOL;
                $sqlUG = "DELETE FROM users_has_users_groups WHERE users_id = ? AND users_groups_id IN (". implode(',', $user_groups_id).")";
                sqlDAL::writeSql($sqlUG, "i", [$row['id']]);
                $count['Deleted']++;
            }
        }
    }
} else {
    die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}
echo "Finish, ". json_encode($count);
