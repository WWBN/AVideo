<?php

//streamer config
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

echo "Start fixing statistics" . PHP_EOL;

$session_id = array();
$sql = "SELECT distinct(session_id) as session_id FROM videos_statistics ";
$res = sqlDAL::readSql($sql);
$fullData = sqlDAL::fetchAllAssoc($res);
sqlDAL::close($res);
$rows = array();
if ($res != false) {
    foreach ($fullData as $key => $row) {
        $session_id[] = $row['session_id'];
    }
} else {
    die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}


foreach ($session_id as $id) {
    echo "Process session_id = {$id}\n";
    ob_flush();
    $sql = "SELECT distinct(videos_id) as videos_id FROM videos_statistics WHERE session_id = '{$id}'";
    echo $sql . PHP_EOL;
    $res = sqlDAL::readSql($sql);
    $fullData = sqlDAL::fetchAllAssoc($res);
    sqlDAL::close($res);
    $rows = array();
    if ($res != false) {
        foreach ($fullData as $row) {
            $sql2 = "SELECT id FROM videos_statistics WHERE videos_id = {$row['videos_id']} AND session_id = '{$id}' ORDER BY `when` DESC LIMIT 1";
            echo $sql . PHP_EOL;
            $res2 = sqlDAL::readSql($sql2);
            $fullData2 = sqlDAL::fetchAllAssoc($res2);
            sqlDAL::close($res2);
            if ($res != false) {
                foreach ($fullData2 as $key2 => $row2) {
                    $sql = "DELETE FROM videos_statistics ";
                    $sql .= " WHERE  videos_id = {$row['videos_id']} AND session_id = '{$id}' AND id != {$row2['id']} ";

                    echo $sql . PHP_EOL;
                    ob_flush();
                    sqlDAL::writeSql($sql);
                }
            } else {
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        }
    } else {
        die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
    }
}

echo "Finish fixing statistics" . PHP_EOL;
