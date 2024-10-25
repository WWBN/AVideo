<?php
$doNotConnectDatabaseIncludeConfig = 1;
$doNotStartSessionIncludeConfig = 1;
require_once dirname(__FILE__) . '/../../videos/configuration.php';
AVideoPlugin::loadPluginIfEnabled('VideoHLS');
if(class_exists('VideoHLS')){
    // Get client information and the requested key file
    $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $requested_key = $_GET['key'] ?? '';
    
    // Implement your authorization logic
    $authorized = false; // Set this based on your logic
    
    $uri = $_SERVER["HTTP_X_ORIGINAL_URI"];
    
    // Define a regular expression to capture the key and token parts
    $pattern = '#/live/([^/]+)/[0-9]+\.key\?token=([^&]+)#i';
    
    // Match the pattern with the URI
    if (preg_match($pattern, $uri, $matches)) {
        // $matches[1] contains the key
        $key = $matches[1];
        // $matches[2] contains the token
        $token = $matches[2];    
    } 
    if(!empty($token)){
        // Example logic: verify based on IP, user agent, or requested key
        if (VideoHLS::verifyToken($token)) {
            $authorized = true;
        }
    }
    error_log('authorizeKeyAccess: '.json_encode(array($key,$array, $user_agent)));
    if (!$authorized) {
        http_response_code(403);
        $msg = 'authorizeKeyAccess: Access denied ';
        error_log($msg.json_encode(array($_SERVER, $matches)));
        echo $msg;
    }else{
        $msg = 'authorizeKeyAccess: Authorized key='.$key;
        error_log($msg);
        echo $msg;
    }
}else{
    $msg = 'authorizeKeyAccess: VideoHLS is not present ';
    error_log($msg);
    echo $msg;
}

?>
