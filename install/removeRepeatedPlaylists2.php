<?php

//streamer config
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
ob_end_flush();
if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$sql = " select count(*) as total, users_id from playlists where status = 'favorite'  group by users_id";
$res = sqlDAL::readSql($sql);
$fullData = sqlDAL::fetchAllAssoc($res);
sqlDAL::close($res);
$rows = [];
if ($res != false) {
    foreach ($fullData as $key => $row) {
        if ($row['total'] > 1) {
            echo "Process user_id = {$row['users_id']} total={$row['total']} favorite\n";
            ob_flush();
            $sql2 = "SELECT * FROM  playlists WHERE users_id = {$row['users_id']} AND status = 'favorite' ORDER BY modified ";
            $res2 = sqlDAL::readSql($sql2);
            $fullData2 = sqlDAL::fetchAllAssoc($res2);
            sqlDAL::close($res2);
            $rows2 = [];
            if ($res2 != false) {
                $totalRows = count($fullData2);
                foreach ($fullData2 as $key2 => $row2) {
                    if ($key2 !== $totalRows-1) {
                        $sql3 = "DELETE FROM playlists ";
                        echo $sql3. " = {$row2['id']}; users_id = {{$row2['users_id']}}".PHP_EOL;
                        $sql3 .= " WHERE id = ?";
                        sqlDAL::writeSql($sql3, "i", [$row2['id']]);
                    }
                }
            } else {
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        }
    }
} else {
    die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}


$sql = " select count(*) as total, users_id from playlists where status = 'watch_later'  group by users_id";
$res = sqlDAL::readSql($sql);
$fullData = sqlDAL::fetchAllAssoc($res);
sqlDAL::close($res);
$rows = [];
if ($res != false) {
    foreach ($fullData as $key => $row) {
        if ($row['total'] > 1) {
            echo "Process user_id = {$row['users_id']} total={$row['total']} watch_later\n";
            ob_flush();
            $sql2 = "SELECT * FROM  playlists WHERE users_id = {$row['users_id']} AND status = 'watch_later' ORDER BY modified ";
            $res2 = sqlDAL::readSql($sql2);
            $fullData2 = sqlDAL::fetchAllAssoc($res2);
            sqlDAL::close($res2);
            $rows2 = [];
            if ($res2 != false) {
                $totalRows = count($fullData2);
                foreach ($fullData2 as $key2 => $row2) {
                    if ($key2 !== $totalRows-1) {
                        $sql3 = "DELETE FROM playlists ";
                        echo $sql3. " = {$row2['id']}; users_id = {{$row2['users_id']}}".PHP_EOL;
                        $sql3 .= " WHERE id = ?";
                        sqlDAL::writeSql($sql3, "i", [$row2['id']]);
                    }
                }
            } else {
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        }
    }
} else {
    die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}