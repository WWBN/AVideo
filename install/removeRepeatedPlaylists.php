<?php
//streamer config
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$users_ids = [];
$sql = "SELECT distinct(users_id) as users_id FROM  playlists ";
$res = sqlDAL::readSql($sql);
$fullData = sqlDAL::fetchAllAssoc($res);
sqlDAL::close($res);
$rows = [];
if ($res != false) {
    foreach ($fullData as $key => $row) {
        $users_ids[] = $row['users_id'];
    }
} else {
    die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}


foreach ($users_ids as $user_id) {
    echo "Process user_id = {$user_id} favorite\n";
    ob_flush();
    $sql = "SELECT * FROM  playlists WHERE users_id = {$user_id} AND status = 'favorite' ORDER BY created ";
    $res = sqlDAL::readSql($sql);
    $fullData = sqlDAL::fetchAllAssoc($res);
    sqlDAL::close($res);
    $rows = [];
    if ($res != false) {
        foreach ($fullData as $key => $row) {
            if ($key === 0) {
                continue;
            }

            if (!empty(PlayList::getVideosIDFromPlaylistLight($row['id']))) {
                continue;
            }

            $sql = "DELETE FROM playlists ";
            $sql .= " WHERE id = ?";

            echo $sql." = {$row['id']}\n";
            ob_flush();
            sqlDAL::writeSql($sql, "i", [$row['id']]);
        }
    } else {
        die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
    }


    echo "Process user_id = {$user_id} watch_later\n";
    ob_flush();
    $sql = "SELECT * FROM  playlists WHERE users_id = {$user_id} AND status = 'watch_later' ORDER BY created ";
    $res = sqlDAL::readSql($sql);
    $fullData = sqlDAL::fetchAllAssoc($res);
    sqlDAL::close($res);
    $rows = [];
    if ($res != false) {
        foreach ($fullData as $key => $row) {
            if ($key === 0) {
                continue;
            }
            $sql = "DELETE FROM playlists ";
            $sql .= " WHERE id = ?";
            echo $sql." = {$row['id']}\n";
            ob_flush();
            sqlDAL::writeSql($sql, "i", [$row['id']]);
        }
    } else {
        die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
    }
}
