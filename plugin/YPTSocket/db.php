<?php
$db = new PDO('sqlite::memory:');

$schema = file_get_contents($global['systemRootPath'] . 'plugin/YPTSocket/sql.sql');

$db->exec($schema);

$db->sqliteCreateFunction('regexp_like', 'preg_match', 2);

$ignoreColumns = array('conn');

//`resourceId`, `users_id`, `room_users_id`, `videos_id`, `live_key`, `isAdmin`, `user_name`, `browser`, `yptDeviceId`, `client`, `selfURI`, `isCommandLine`, `pageTitle`, `ip`, `location`, `data`
function dbInsertConnection($array) {
    global $db, $ignoreColumns;
    $columns = array();
    $values = array();
    $holders = array();
    foreach ($array as $key => $value) {
        if (in_array($key, $ignoreColumns)) {
            continue;
        }
        $columns[] = $key;

        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
            /*
              var_dump($key);
              var_dump($value);
              exit;
             * 
             */
        }
        if (is_null($value)) {
            $value = '';
        }
        $holders[] = ":{$key}";
        $values[] = $value;
    }
    $sql = 'INSERT INTO `connections` '
            . '(`' . implode('`, `', $columns) . '`) VALUES '
            . "(" . implode(", ", $holders) . ");";
    try {
        $sth = $db->prepare($sql);
        foreach ($values as $key => $value) {
            $sth->bindValue($holders[$key], $value, PDO::PARAM_STR);
        }
        $sth->execute();
        return true;
    } catch (Exception $exc) {
        echo PHP_EOL;
        echo PHP_EOL;
        var_dump($sql);
        var_dump($sth->errorInfo());
        $count = 0;
        foreach ($holders as $key => $value) {
            $count++;
            echo "{$count} => {$value} => {$values[$key]}".PHP_EOL;
        }
        echo PHP_EOL;
        echo $exc->getTraceAsString();
        echo PHP_EOL;
        echo $sql;
        echo PHP_EOL;
        echo PHP_EOL;
        return false;
    }
}


function dbGetRowFromResourcesId($resourceId) {
    global $db;
    $sql = "SELECT * FROM `connections` "
            . " WHERE resourceId = '{$resourceId}' ";
    $sth = $db->prepare($sql);
    $sth->execute();
    return $sth->fetch();
}

function dbDeleteConnection($resourceId) {
    global $db;
    $sql = "DELETE FROM `connections` WHERE resourceId = {$resourceId} ";
    $db->exec($sql);
}

function dbGetAll() {
    global $db;
    $sql = "SELECT * FROM `connections` ";
    $sth = $db->prepare($sql);
    $sth->execute();
    return $sth->fetchAll();
}

function dbGetTotalInVideos() {
    global $db;
    $sql = "SELECT videos_id, count(resourceId) as total FROM `connections` "
            . "WHERE videos_id IS NOT NULL AND videos_id != '' "
            . "GROUP BY videos_id ";
    $sth = $db->prepare($sql);
    $sth->execute();
    return $sth->fetchAll();
}

function dbGetTotalInLive() {
    global $db;
    $sql = "SELECT live_key_servers_id, live_key, live_servers_id, count(resourceId) as total FROM `connections` ";
    $sql .= "WHERE live_key_servers_id IS NOT NULL AND live_key_servers_id != '' ";
    $sql .= "GROUP BY live_key_servers_id ";
    $sth = $db->prepare($sql);
    $sth->execute();
    return $sth->fetchAll();
}

function dbGetTotalInLiveLink() {
    global $db;
    $sql = "SELECT liveLink, count(resourceId) as total FROM `connections` "
            . "WHERE liveLink IS NOT NULL AND liveLink != '' "
            . "GROUP BY liveLink ";
    $sth = $db->prepare($sql);
    $sth->execute();
    return $sth->fetchAll();
}

function dbGetClassToUpdate() {
    $return['class_to_update'] = array();
    $rows = dbGetTotalInVideos();
    foreach ($rows as $client) {
        $keyName = getSocketVideoClassName($client['videos_id']);
        $return['class_to_update'][$keyName] = $client['total'];
    }
    $rows = dbGetTotalInLive();
    foreach ($rows as $client) {
        $keyName = getSocketLiveClassName($client['live_key'], $client['live_servers_id']);
        $return['class_to_update'][$keyName] = $client['total'];
    }
    $rows = dbGetTotalInLiveLink();
    foreach ($rows as $client) {
        $keyName = getSocketLiveLinksClassName($client['liveLink']);
        $return['class_to_update'][$keyName] = $client['total'];
    }
    return $return;
}

function dbGetAllResourcesIdFromUsersId($users_id) {
    global $db;
    $sql = "SELECT resourceId FROM `connections` "
            . " WHERE users_id = {$users_id} ";
    $sth = $db->prepare($sql);
    $sth->execute();
    return $sth->fetchAll();
}

function dbGetAllResourceIdFromSelfURI($selfURI) {
    global $db;
    $sql = "SELECT resourceId FROM `connections` "
            . " WHERE regexp_like('{$selfURI}', selfURI) ";
    echo $sql.PHP_EOL;
    $sth = $db->prepare($sql);
    $sth->execute();
    $result = $sth->fetchAll();
    return $result;
}

function dbGetAllResourcesIdFromVideosId($videos_id) {
    global $db;
    $sql = "SELECT resourceId FROM `connections` "
            . " WHERE videos_id = {$videos_id} ";
    $sth = $db->prepare($sql);
    $sth->execute();
    return $sth->fetchAll();
}

function dbGetAllResourcesIdFromLive($live_key, $live_servers_id) {
    global $db;
    $sql = "SELECT resourceId FROM `connections` "
            . " WHERE live_key_servers_id = '{$live_key}_{$live_servers_id}' ";
    $sth = $db->prepare($sql);
    $sth->execute();
    return $sth->fetchAll();
}

function dbGetTotalUniqueDevices() {
    global $db;
    $sql = "SELECT count(yptDeviceId) as total "
            . "FROM `connections` "
            . "WHERE yptDeviceId IS NOT NULL AND yptDeviceId != '' ";
    $sth = $db->prepare($sql);
    $sth->execute();
    $result = $sth->fetch();
    return $result['total'];
}

function dbGetUniqueUsers() {
    global $db;
    $sql = "SELECT distinct(users_id) "
            . "FROM `connections`"
            . "WHERE users_id IS NOT NULL AND users_id != '' ";
    $sth = $db->prepare($sql);
    $sth->execute();
    return $sth->fetchAll();
}

function dbGetTotalUniqueUsers() {
    $result = dbGetUniqueUsers();
    return count($result);
}

function dbGetTotalPerVideosId() {
    global $db;
    $sql = "SELECT count(yptDeviceId) as total "
            . "FROM `connections` "
            . "WHERE yptDeviceId IS NOT NULL AND yptDeviceId != '' ";
    $sth = $db->prepare($sql);
    $sth->execute();
    $result = $sth->fetch();
    return $result['total'];
}

function dbIsUserOnLine($users_id) {
    return !empty(dbGetAllResourcesIdFromUsersId($users_id));
}
