<?php

if (!isset($phpbb_root_path)) {
    if (php_sapi_name() === 'cli') {
        if (file_exists('config.php') && file_exists('index.php')) {
            if (!file_exists('index.original.php')) {
                copy('index.php', 'index.original.php');
            }
            $content = file_get_contents('index.original.php');
            $newContent = str_replace('$user->session_begin();', 'require_once $phpbb_root_path . \'avideoLogin.php\';' . PHP_EOL . '$user->session_begin();', $content);
            file_put_contents('index.php', $newContent);
        } else {
            die('Must be inside phpBB directory');
        }
    } else {
        die('Must run inside phpBB');
    }
}

function getRequest($name) {
    global $request;
    return $request->variable($name, '', true, \phpbb\request\request_interface::REQUEST);
}

function getCookie($name) {
    global $request;
    return $request->variable($name, '', true, \phpbb\request\request_interface::COOKIE);
}
$avideoURL = "{webSiteRootURL}";
$userR = getRequest('user');
$passR = getRequest('pass');
if (!empty($userR) && !empty($passR)) {
    $loginURL = "{$avideoURL}objects/login.json.php?user=" . urlencode($userR) . "&pass=" . urlencode($passR).'&encodedPass=1';
    $content = file_get_contents($loginURL);
    if (!empty($content)) {
        $json = json_decode($content);
        if (!empty($json->id)) {
            $dbuserToLogin = $json->user;
            $email = $json->email;
        }
    }
}
if (!empty($dbuserToLogin)) {

    require_once $phpbb_root_path . 'config.' . $phpEx;
    include($phpbb_root_path . '/includes/functions_user.' . $phpEx);

    // Create connection
    $mysqli = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname);

    if ($result = $mysqli->query("SELECT * FROM {$table_prefix}users WHERE username = '{$dbuserToLogin}' LIMIT 1")) {
        if ($row = $result->fetch_assoc()) {
            $users_id = $row['user_id'];
        } else {
            $user_row = array(
                'username' => $dbuserToLogin,
                'group_id' => 2,
                'user_email' => $email,
                'user_type' => 0);
            $users_id = user_add($user_row);
        }
    }

    if (!empty($users_id) && $result = $mysqli->query("SELECT * FROM {$table_prefix}config WHERE config_name = 'cookie_name' LIMIT 1")) {
        if ($result->num_rows > 0) {
            if ($row = $result->fetch_assoc()) {
                //var_dump($row);exit;
                $cookie_sid = getCookie("{$row['config_value']}_sid");
                if (!empty($cookie_sid)) {
                    setcookie("{$row['config_value']}_u", $users_id, time() + (86400 * 30), "/"); // 86400 = 1 day
                    $mysqli->query("UPDATE `{$table_prefix}sessions` SET `session_user_id` = '{$users_id}' WHERE (`session_id` = '{$cookie_sid}')");
                    //var_dump($cookie_sid, $dbuserToLogin, $email);
                }
            }
        } else {
            die('No record found.<br />');
        }
    }
}
?>