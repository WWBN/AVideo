<?php

try {
    $db = new PDO('sqlite::memory:');
} catch (Exception $exc) {
    riseSQLiteError();
    echo $exc->getTraceAsString();
}

$schema = file_get_contents($global['systemRootPath'] . 'plugin/YPTSocket/sql.sql');

$db->exec($schema);

$db->sqliteCreateFunction('regexp_like', 'preg_match', 2);

$ignoreColumns = array('conn');

//`resourceId`, `users_id`, `room_users_id`, `videos_id`, `live_key`, `isAdmin`, `user_name`, `browser`, `yptDeviceId`, `client`, `selfURI`, `isCommandLine`, `page_title`, `ip`, `location`, `data`
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
        var_dump($exc->getMessage());
        $count = 0;
        foreach ($holders as $key => $value) {
            $count++;
            echo "{$count} => {$value} => {$values[$key]}" . PHP_EOL;
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
    echo $sql . PHP_EOL;
    $sth = $db->prepare($sql);
    $sth->execute();
    $result = $sth->fetchAll();
    echo PHP_EOL.'-----------------'.PHP_EOL.PHP_EOL.PHP_EOL;
    var_dump($result);
    dbGetAllResourceIdAndSelfURI();
    echo PHP_EOL.'-----------------'.PHP_EOL.PHP_EOL.PHP_EOL;
    return $result;
}


function dbGetAllResourceIdAndSelfURI() {
    global $db;
    $sql = "SELECT resourceId, selfURI FROM `connections` ";
    echo $sql . PHP_EOL;
    $sth = $db->prepare($sql);
    $sth->execute();
    $result = $sth->fetchAll();
    echo PHP_EOL.'-----------------'.PHP_EOL.PHP_EOL.PHP_EOL;
    var_dump($result);
    echo PHP_EOL.'-----------------'.PHP_EOL.PHP_EOL.PHP_EOL;
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

function dbGetDBTotals() {
    global $db;
    $users_uri = array();
    $sql = "SELECT * "
            . "FROM `connections` "
            . "WHERE selfURI IS NOT NULL AND selfURI != '' ORDER BY selfURI ";
    $sth = $db->prepare($sql);
    $sth->execute();
    $rows = $sth->fetchAll();
    $videos = array();
    foreach ($rows as $client) {
        $index = md5($client['selfURI']);
        if (!isset($users_uri[$index])) {
            $users_uri[$index] = array();
        }
        if (!isset($users_uri[$index][$client['yptDeviceId']])) {
            $users_uri[$index][$client['yptDeviceId']] = array();
        }
        if (!isset($users_uri[$index][$client['yptDeviceId']][$client['users_id']])) {
            $users_uri[$index][$client['yptDeviceId']][$client['users_id']] = array();
        }

        $location = false;
        if(!empty($client['location'])){
            $location = array('country_code'=>$client['country_code'], 'country_name'=>$client['country_name']);
        }

        $users_uri[$index][$client['yptDeviceId']][$client['users_id']] = array('resourceId' => $client['resourceId'],
            'users_id' => $client['users_id'],
            'room_users_id' => $client['room_users_id'],
            'videos_id' => $client['videos_id'],
            'live_key_servers_id' => $client['live_key_servers_id'],
            'liveLink' => $client['liveLink'],
            'isAdmin' => $client['isAdmin'],
            'live_key' => $client['live_key'],
            'live_servers_id' => $client['live_servers_id'],
            'user_name' => $client['user_name'],
            'browser' => $client['browser'],
            'yptDeviceId' => $client['yptDeviceId'],
            'client' => $client['client'],
            'selfURI' => $client['selfURI'],
            'isCommandLine' => $client['isCommandLine'],
            'page_title' => $client['page_title'],
            'os' => $client['os'],
            'country_code' => $client['country_code'],
            'country_name' => $client['country_name'],
            'identification' => $client['identification'],
            'ip' => $client['ip'],
            'location' => $location,
            'client' => array('browser'=>$client['browser'], 'os'=>$client['os']));

        if(!empty($client['videos_id'])){
            $videoIndex = "total_on_videos_id_{$client['videos_id']}";
            if(empty($videos[$videoIndex])){
                $videos[$videoIndex] = 0;
            }
            $videos[$videoIndex]++;
        }
    }

    return array('users_uri'=>$users_uri, 'videos'=>$videos);
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

function dbGetTotalConnections() {
    global $db;
    $sql = "SELECT count(resourceId) as total "
            . "FROM `connections` WHERE (isCommandLine = 0 OR isCommandLine IS NULL)";
    $sth = $db->prepare($sql);
    $sth->execute();
    $result = $sth->fetch();
    return $result['total'];
}

function dbGetUniqueUsers() {
    global $db;
    $sql = "SELECT distinct(users_id),
            isAdmin, live_key_servers_id,
            videos_id, selfURI, country_name,
            page_title, resourceId, room_users_id,
            chat_is_banned, identification "
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
