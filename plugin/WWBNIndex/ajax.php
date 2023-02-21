<?php 

if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}

require_once $global['systemRootPath']. "plugin/WWBNIndex/Objects/WWBNIndexModel.php";
require_once $global['systemRootPath']. "objects/Channel.php";

$configuration = new Configuration();
$model = new WWBNIndexModel();

$r = $_POST;

if ($r['action'] == "authAccount") {
    $plugin_data = $model->getPluginData();
    $object_data = json_decode($plugin_data[0]['object_data']);

    $email = $configuration->getContactEmail();
    $username = strtolower(preg_replace('/\s+/', "_", trim($configuration->getWebSiteTitle())));
    $data = array(
        "apiName"           => "signupAccount",
        "username" 			=> $username, 
        "email" 			=> $email, 
        "name"              => $configuration->getWebSiteTitle(),
        "siteacctid"        => 4541, // WWBN account id
        "siteid_fk"         => 152, // WWBN
        "sitelinkid_fk"     => 2504, // WWBN
        "instance_id"       => 10054, // WWBN
        // "account_type"      => 3,
        "logo"              => $global['webSiteRootURL'].$configuration->getLogo(),
        "icon"              => $configuration->getFavicon(true),
        "avideo_id"         => getPlatformId(),
        "host"              => parse_url($global['webSiteRootURL'])['host'],
    );

    if (isset($object_data->yp_token) && $object_data->yp_token != "") {
       $data['yp_token'] = $object_data->yp_token;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://wwbn.com/api/function.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response  = json_decode(curl_exec($ch));
    if (curl_errno($ch)) {
        echo json_encode(array("error" => true, "title" => "CURL Error", "message" => "Ops! Something wrong with auth api.")); die(); // . curl_error($ch);
    }
    curl_close ($ch);

    if ($response->error == false) {
        $new_object_data = array();
        foreach ($object_data as $key => $obj) {
            $new_object_data[$key] = $obj; 
        }
        $new_object_data["username"] = $username;
        $new_object_data["email"] = $email;
        $new_object_data["password"] = $response->data->password;
        $new_object_data["searchmercials_password"] = $response->data->searchmercials_password;
        if (isset($response->data->token)) {
            $new_object_data["yp_token"] = $response->data->token;
        }
        $update = $model->updateObjectData(json_encode($new_object_data));
        if ($update) {
            echo json_encode($response); die();
        } else {
            echo json_encode(array("error" => true, "title" => "Database Error", "message" => "Ops! Something went wrong while saving data.")); die();
        }
    } else {
        echo json_encode($response); die();
    }

} else if ($r['action'] == "submitVerificationCode") {

    $plugin_data = $model->getPluginData();
    $object_data = json_decode($plugin_data[0]['object_data']);

    // API Update verify
    $verify = verifyEmail($object_data, $r['otp']);
    $verify_decoded = json_decode($verify);
    if (isset($verify_decoded->error) && $verify_decoded->error == false) {
        $new_object_data = array();
        foreach ($object_data as $key => $obj) {
            $new_object_data[$key] = $obj; 
        }
        $new_object_data['verified'] = true;
        $update = $model->updateObjectData(json_encode($new_object_data));
        if ($update) {
            echo json_encode(array("error" => false, "title" => "Email Verified", "message" => "Email has been verified successfully!")); die();
        } else {
            echo json_encode(array("error" => true, "title" => "Database Error", "message" => "Ops! Something wrong saving in database.")); die();
        }
    } else {
        echo $verify; die();
    }

} else if ($r['action'] == "resendVerificationCode") {

    echo resendCode($configuration->getContactEmail()); die();

} else if ($r['action'] == "submitIndex") {
    
    $object_data = $model->getPluginData()[0]['object_data'];
    if ($object_data == "" || $object_data == null) {
        echo json_encode(array("error" => true, "title" => "Plugin data not Found", "message" => "Ops! Something went wrong. Please refresh the page and try again.")); die();
    }
    $object_data = json_decode($object_data);  // convert string to object
    $email = $object_data->email;
    $user = getYouPortalUser($object_data);
    if ($user->error == true) {
        echo json_encode(array("error" => true, "title" => "API Error", "message" => "Ops! Something went wrong. Please refresh the page and try again.")); die();
    }
    $data = array(
        "apiName"           => "submitIndex",
        "host"              => parse_url($global['webSiteRootURL'])['host'],
        "avideo_id"         => getPlatformId(),
        "engine_name"       => ($r['engine_name'] != "") ? $r['engine_name'] : $configuration->getWebSiteTitle(),
        "engine_logo"       => $global['webSiteRootURL'].$configuration->getLogo(),
        "engine_icon"       => $configuration->getFavicon(true),
        "content_type"      => 4, // 1 = Text, 2 = Video, 3 = Audio, 4 = Audio and Video
        "feed_url"          => $global['webSiteRootURL']. "plugin/API/get.json.php?APIName=video&rowCount=20&search=[TERMS]",
        "detail_url"        => $global['webSiteRootURL']. "plugin/API/get.json.php?APIName=video&videos_id=[LID]",
        "affiliates"        => array(1), // 1 = searchtube
        "sitelinkid_fk"     => 2504, // WWBN
        "siteacctid_fk"     => 4541, // WWBN account id
        "acctkey_fk"        => $user->data->acct_idkey, // WWBN
        "validation_token"  => getWWBNToken(),
        "email"             => $email,
        "version"		    => $configuration->getVersion(),
        "users"			    => User::getAllUsers(),
        "plugins"		    => Plugin::getAvailablePluginsBasic(),
        "total_videos"	    => Video::getTotalVideos('', false, true, true),
        "total_users"	    => User::getTotalUsers(true, 'a'),
        "total_channels"    => Channel::getTotalChannels(),
        "language"		    => $configuration->getLanguage(),
    );

    if (isset($object_data->yp_token) && $object_data->yp_token != "") {
        $data['yp_token'] = $object_data->yp_token;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://wwbn.com/api/function.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = json_decode(curl_exec($ch));
    if (curl_errno($ch)) {
        echo json_encode(array("error" => true, "title" => "CURL Error", "message" => "Ops! Something wrong with indexing api.", "curl_error" => curl_error($ch))); die(); // . curl_error($ch);
    }
    curl_close ($ch);
    if (isset($response->error) && $response->error == false) {
        $new_object_data = array();
        $organic = false;
        foreach ($object_data as $key => $obj) {
            $new_object_data[$key] = $obj; 
            if ($key == "organic") {
                $organic = true;
            }
        }
        if (!$organic) {
            $new_object_data['engine_name'] = ($r['engine_name'] != "") ? $r['engine_name'] : $configuration->getWebSiteTitle();
        } else {
            unset($new_object_data['organic']);
        }

        if (isset($object_data->yp_token)) {
            $new_object_data["yp_token"] = $response->token;
        }
        $update = $model->updateObjectData(json_encode($new_object_data));
        if ($update) {
            echo json_encode($response); die();
        } else {
            echo json_encode(array("error" => true, "title" => "Database Error", "message" => "Ops! Something went wrong while saving data.")); die();
        }
    }
    echo json_encode($response); die();

} else if ($r['action'] == "getIndexTermsAndConditions") {
    $plugin_data = $model->getPluginData();
    $object_data = json_decode($plugin_data[0]['object_data']);
    $data = array(
        "apiName"       => "getIndexTermsAndConditions",
        "siteid_fk"     => 152, // WWBN
        "sitelinkid_fk" => 2504, // WWBN
        "avideo_id"     => getPlatformId(),
    );
    if (isset($object_data->yp_token) && $object_data->yp_token != "") {
        $data['yp_token'] = $object_data->yp_token;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://wwbn.com/api/function.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = json_decode(curl_exec($ch));
    if (curl_errno($ch)) {
        echo json_encode(array("error" => true, "title" => "CURL Error", "message" => "Ops! Something wrong with terms and conditions api.")); die(); // . curl_error($ch);
    }
    curl_close ($ch);
    echo json_encode($response); die();
    
} else if ($r['action'] == "reIndex") {

    $plugin_data = $model->getPluginData();
    $object_data = json_decode($plugin_data[0]['object_data']);
    echo reIndex($object_data); die();

} else if ($r['action'] == "unIndex") {
    
    $plugin_data = $model->getPluginData();
    $object_data = json_decode($plugin_data[0]['object_data']);
    echo unIndex($object_data); die();

} else if ($r['action'] == "changePluginStatus") {

    $plugin_data = $model->getPluginData();
    if ($plugin_data) {
        $object_data = $plugin_data[0]['object_data'];
        if ($object_data != "" && $object_data != null) {
            $object_data = json_decode($object_data);
            if (isset($object_data->engine_name)) {
                $feed_status = getFeedStatus(parse_url($object_data, $global['webSiteRootURL'])['host']);
                if (!$feed_status->error) {
                    if ($r['enabled'] == "true") { 
                        if ($feed_status->indexed == "false" && $feed_status->status == "inactive") {
                            // re-index platform
                            reIndex($object_data);
                        }
                    } else {
                        if ($feed_status->indexed == "true" && ($feed_status->status == "active" || $feed_status->status == "review")) {
                            // unindex platform
                            unIndex($object_data);
                        }
                    }
                } else {
                    if ($feed_status->message == "URL not match") {
                        // re-index platform
                        reIndex($object_data);
                    }
                }
            }
        }
    }
    // ClearCache
    include($global['systemRootPath']."objects/configurationClearCache.json.php?FirstPage=1");
} else if ($r['action'] == "refreshToken") {
    $object_data = $model->getPluginData()[0]['object_data'];
    if ($object_data == "" || $object_data == null) {
        echo json_encode(array("error" => true, "title" => "Plugin data not Found", "message" => "Ops! Something went wrong. Please refresh the page and try again.")); die();
    }
    $object_data = json_decode($object_data);
    $data = array(
        "apiName"       => "refreshToken",
        "avideo_id"     => getPlatformId(),
    );

    if (isset($object_data->yp_token) && $object_data->yp_token != "") {
        $data['yp_token'] = $object_data->yp_token;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://wwbn.com/api/function.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = json_decode(curl_exec($ch));
    if (curl_errno($ch)) {
        echo json_encode(array("error" => true, "title" => "CURL Error", "message" => "Ops! Something wrong with re-index api.")); die();
    }
    curl_close ($ch);

    if (isset($response->error) && $response->error == false) {
        $new_object_data = array();
        foreach ($object_data as $key => $obj) {
            $new_object_data[$key] = $obj; 
        }
        $new_object_data['yp_token'] = $response->token;
        $update = $model->updateObjectData(json_encode($new_object_data));
        if ($update) {
            echo json_encode($response); die();
        } else {
            echo json_encode(array("error" => true, "title" => "Database Error", "message" => "Ops! Something went wrong while saving data.")); die();
        }
    } else {
        echo json_encode(array("error" => true, "title" => $response->title, "message" => $response->message)); die();
    }
}

// if (isset($return)) {
//     echo json_encode($return); die();
// } else {
//     echo json_encode(array()); die();
// }

function reIndex($object_data) {
    global $global, $configuration, $model;
    $email = $configuration->getContactEmail();
    $title = $configuration->getWebSiteTitle();
    $data = array(
        "apiName"       => "reIndex",
        "avideo_id"     => getPlatformId(),
        "engine_name"   => $title, //$object_data->engine_name
        "engine_logo"   => $global['webSiteRootURL'].$configuration->getLogo(),
        "engine_icon"   => $configuration->getFavicon(true),
        "feed_url"      => $global['webSiteRootURL']. "plugin/API/get.json.php?APIName=video&rowCount=20&search=[TERMS]",
        "detail_url"    => $global['webSiteRootURL']. "plugin/API/get.json.php?APIName=video&videos_id=[LID]",
        "email"         => $email, //$object_data->email 
        "acct_email"    => $object_data->email, 
        "host"          => parse_url($global['webSiteRootURL'])['host'],
    );

    if (isset($object_data->yp_token) && $object_data->yp_token != "") {
        $data['yp_token'] = $object_data->yp_token;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://wwbn.com/api/function.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = json_decode(curl_exec($ch));
    if (curl_errno($ch)) {
        return json_encode(array("error" => true, "title" => "CURL Error", "message" => "Ops! Something wrong with re-index api."));
    }
    curl_close ($ch);

    if (isset($response->error) && $response->error == false) {
        $new_object_data = array();
        foreach ($object_data as $key => $obj) {
            $new_object_data[$key] = $obj; 
            if ($key == "engine_name") {
                $new_object_data[$key] = $title;
            }
            if ($key == "email") {
                $new_object_data[$key] = $email;
            }
        }
        $update = $model->updateObjectData(json_encode($new_object_data));
        if ($update) {
            return json_encode($response);
        } else {
            return json_encode(array("error" => true, "title" => "Database Error", "message" => "Ops! Something went wrong while saving data."));
        }
    } else {
        return json_encode(array("error" => true, "title" => $response->title, "message" => $response->message));
    }
}

function unIndex($object_data) {
    $data = array(
        "apiName"       => "unIndex",
        // "engine_name"   => $object_data->engine_name, 
        "avideo_id"     => getPlatformId(),
        "email"         => $email, //$object_data->email 
        "acct_email"    => $object_data->email,  
    );

    if (isset($object_data->yp_token) && $object_data->yp_token != "") {
        $data['yp_token'] = $object_data->yp_token;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://wwbn.com/api/function.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = json_decode(curl_exec($ch));
    if (curl_errno($ch)) {
        return json_encode(array("error" => true, "title" => "CURL Error", "message" => "Ops! Something wrong with unindex api."));
    }
    curl_close ($ch);
    return json_encode($response);
}

function getWWBNToken() {
    global $global;
    $obj = new stdClass();
    $obj->plugin = "WWBN";
    $obj->webSiteRootURL = $global['webSiteRootURL'];
    $obj->time = time();
    return encryptString($obj);
}

function resendCode($email) {
    global $model;
    $object_data = $model->getPluginData()[0]['object_data'];
    if ($object_data == "" || $object_data == null) {
        echo json_encode(array("error" => true, "title" => "Plugin data not Found", "message" => "Ops! Something went wrong. Please refresh the page and try again.")); die();
    }
    $object_data = json_decode($object_data);
    if ($object_data == "" || $object_data == null) {
        return json_encode(array("error" => true, "title" => "Plugin data not Found", "message" => "Ops! Something went wrong. Please refresh the page and try again."));
    }
    $data = array(
        "apiName"   => "sendOTPCode",
        "email"     => $email,
        "avideo_id" => getPlatformId()
    );
    if (isset($object_data->yp_token) && $object_data->yp_token != "") {
        $data['yp_token'] = $object_data->yp_token;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://wwbn.com/api/function.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response  = json_decode(curl_exec($ch));
    if (curl_errno($ch)) {
        return json_encode(array("error" => true, "title" => "CURL Error", "message" => "Ops! Something wrong with resend code api."));
    }
    return json_encode($response);
}

function verifyEmail($object_data, $otp) {
    $email = $object_data->email;
    $username = $object_data->username;
    $data = array(
        "apiName"       => "verifyEmail",
        "email"         => $email,
        "username"      => $username,
        "siteacctid"    => 4541, // WWBN account id
        "siteid_fk"     => 152, // WWBN
        "sitelinkid_fk" => 2504, // WWBN
        "avideo_id"     => getPlatformId(),
        "otp"           => $otp
    );
    if (isset($object_data->yp_token) && $object_data->yp_token != "") {
        $data['yp_token'] = $object_data->yp_token;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://wwbn.com/api/function.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response  = json_decode(curl_exec($ch));
    if (curl_errno($ch)) {
        return json_encode(array("error" => true, "title" => "CURL Error", "message" => "Ops! Something wrong with verify email api."));
    }
    return json_encode($response);
}

function getYouPortalUser($object_data) {
    $data = array(
        "apiName"   => "getUser",
        "email"     => $object_data->email,
        "avideo_id" => getPlatformId()
    );
    if (isset($object_data->yp_token) && $object_data->yp_token != "") {
        $data['yp_token'] = $object_data->yp_token;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://wwbn.com/api/function.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = json_decode(curl_exec($ch));
    if (curl_errno($ch)) {
        return (object) array("error" => true, "title" => "CURL Error", "message" => "Ops! Something wrong with get user api.");
    }
    curl_close ($ch);
    return $response;
}

function getFeedStatus($object_data, $host) {
    global $configuration;
    $data = array(
        "apiName"       => "getFeedStatus",
        "engine_name"   => $configuration->getWebSiteTitle(), //$engine_name,
        "avideo_id"     => getPlatformId(),
        "host"          => $host,
    );
    if (isset($object_data->yp_token) && $object_data->yp_token != "") {
        $data['yp_token'] = $object_data->yp_token;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://wwbn.com/api/function.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = json_decode(curl_exec($ch));
    if (curl_errno($ch)) {
        return (object) array("error" => true, "title" => "CURL Error", "message" => "Ops! Something wrong with get feed api.");
    }
    curl_close($ch);
    return $response;
}

?>