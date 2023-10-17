<?php 

if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}

require_once $global['systemRootPath']. "plugin/WWBNIndex/WWBNIndex.php";
require_once $global['systemRootPath']. "objects/Channel.php";
require_once $global['systemRootPath']. "plugin/LoginControl/pgp/functions.php";

$configuration = new AVideoConf();
$wwbnIndex = new WWBNIndex();
$wwbnIndexModel = new WWBNIndexModel();
$platformID = getPlatformId();

$r = $_POST;

if (empty($_SERVER['SERVER_NAME']) || $_SERVER['SERVER_NAME'] === 'localhost' || filter_var($_SERVER['SERVER_NAME'], FILTER_VALIDATE_IP) || $wwbnIndex->check_site_availability($_SERVER['HTTP_HOST']) != 200) {
    echo json_encode(array("error" => true, "title" => "Site not accessible", "message" => "Please make sure your site is viewable in public.")); die();
}

if ($r['action'] == "authAccount") {

    $validate = json_decode(exchangeKeys());
    if ($validate->error) {
        echo json_encode($validate); die();
    }

    // remove this variable to get updated object_data
    unset($readSqlCached);
    unset($fetchAllAssoc_cache);

    $plugin_data = $wwbnIndexModel->getPluginData();
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
        "avideo_id"         => $platformID,
        "host"              => parse_url($global['webSiteRootURL'])['host'],
    );
    
    $response = json_decode(postVariables("https://wwbn.com/api/function.php", $data, false));

    if ($response->error == false) {
        $new_object_data = array();
        foreach ($object_data as $key => $obj) {
            $new_object_data[$key] = $obj; 
        }
        $new_object_data["username"] = $username;
        $new_object_data["email"] = $email;
        $new_object_data["password"] = $response->data->password;
        $new_object_data["searchmercials_password"] = $response->data->searchmercials_password;
        $update = $wwbnIndexModel->updateObjectData(json_encode($new_object_data));
        if ($update) {
            echo json_encode($response); die();
        } else {
            echo json_encode(array("error" => true, "title" => "Database Error", "message" => "Ops! Something went wrong while saving data.")); die();
        }
    } else {
        echo json_encode($response); die();
    }

} else if ($r['action'] == "submitVerificationCode") {

    $validate = json_decode(exchangeKeys());
    if ($validate->error) {
        echo json_encode($validate); die();
    }
    // remove this variable to get updated object_data
    unset($readSqlCached);
    unset($fetchAllAssoc_cache);

    $plugin_data = $wwbnIndexModel->getPluginData();
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
        $update = $wwbnIndexModel->updateObjectData(json_encode($new_object_data));
        if ($update) {
            echo json_encode(array("error" => false, "title" => "Email Verified", "message" => "Email has been verified successfully!")); die();
        } else {
            echo json_encode(array("error" => true, "title" => "Database Error", "message" => "Ops! Something wrong saving in database.")); die();
        }
    } else {
        echo $verify; die();
    }

} else if ($r['action'] == "resendVerificationCode") {

    $validate = json_decode(exchangeKeys());
    if ($validate->error) {
        echo json_encode($validate); die();
    }

    // remove this variable to get updated object_data
    unset($readSqlCached);
    unset($fetchAllAssoc_cache);

    echo resendCode($configuration->getContactEmail()); die();

} else if ($r['action'] == "submitIndex") {
    
    $validate = json_decode(exchangeKeys());
    if ($validate->error) {
        echo json_encode($validate); die();
    }

    // remove this variable to get updated object_data
    unset($readSqlCached);
    unset($fetchAllAssoc_cache);

    $object_data = $wwbnIndexModel->getPluginData()[0]['object_data'];
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
        "avideo_id"         => $platformID,
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
    
    $response = json_decode(postVariables("https://wwbn.com/api/function.php", $data, false));

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
        $update = $wwbnIndexModel->updateObjectData(json_encode($new_object_data));
        if ($update) {
            echo json_encode($response); die();
        } else {
            echo json_encode(array("error" => true, "title" => "Database Error", "message" => "Ops! Something went wrong while saving data.")); die();
        }
    }
    echo json_encode($response); die();

} else if ($r['action'] == "getIndexTermsAndConditions") {

    $validate = json_decode(exchangeKeys());
    if ($validate->error) {
        echo json_encode($validate); die();
    }

    $data = array(
        "apiName"       => "getIndexTermsAndConditions",
        "siteid_fk"     => 152, // WWBN
        "sitelinkid_fk" => 2504, // WWBN
        "avideo_id"     => $platformID,
    );

    echo postVariables("https://wwbn.com/api/function.php", $data, false); die();
    
} else if ($r['action'] == "reIndex") {

    $validate = json_decode(exchangeKeys());
    if ($validate->error) {
        echo json_encode($validate); die();
    }

    // remove this variable to get updated object_data
    unset($readSqlCached);
    unset($fetchAllAssoc_cache);

    $plugin_data = $wwbnIndexModel->getPluginData();
    $object_data = json_decode($plugin_data[0]['object_data']);

    echo reIndex($object_data); die();

} else if ($r['action'] == "unIndex") {

    $validate = json_decode(exchangeKeys());
    if ($validate->error) {
        echo json_encode($validate); die();
    }

    // remove this variable to get updated object_data
    unset($readSqlCached);
    unset($fetchAllAssoc_cache);

    $plugin_data = $wwbnIndexModel->getPluginData();
    $object_data = json_decode($plugin_data[0]['object_data']);
    
    echo unIndex($object_data); die();

} else if ($r['action'] == "changePluginStatus") {

    $plugin_data = $wwbnIndexModel->getPluginData();
    if ($plugin_data) {
        $object_data = $plugin_data[0]['object_data'];
        if ($object_data != "" && $object_data != null) {
            $object_data = json_decode($object_data);
            if (isset($object_data->engine_name)) {
                $feed_status = getFeedStatus(parse_url($global['webSiteRootURL'])['host']);
                $validate = json_decode(exchangeKeys($plugin_data));
                if (!$feed_status->error) {
                    if ($r['enabled'] == "true") { 
                        if ($feed_status->indexed == "false" && $feed_status->status == "inactive") {
                            
                            if ($validate->error) {
                                echo json_encode($validate); die();
                            }

                            // re-index platform
                            reIndex($object_data);
                        }
                    } else {
                        if ($feed_status->indexed == "true" && ($feed_status->status == "active" || $feed_status->status == "review")) {
                            
                            if ($validate->error) {
                                echo json_encode($validate); die();
                            }
                            // unindex platform
                            unIndex($object_data);
                        }
                    }
                } else {
                    if ($feed_status->message == "URL not match") {

                        if ($validate->error) {
                            echo json_encode($validate); die();
                        }
                        // re-index platform
                        reIndex($object_data);
                    }
                }
            }
        }
    }
    // ClearCache
    include($global['systemRootPath']."objects/configurationClearCache.json.php?FirstPage=1");

} else if ($r['action'] == "requestResetKeys") {

    $plugin_data = $wwbnIndexModel->getPluginData();
    $object_data = json_decode($plugin_data[0]['object_data']);

    if (isset($object_data->keys->requestReset) && $object_data->keys->requestReset) {
        echo json_encode(array("error" => true, "type" => "info", "title" => "Pending Request", "message" => "You have already submitted a request. Please wait or email us here to get an update (mail@wwbn.com)")); die();
    }

    $data = array(
        "apiName"       => "requestResetKeys",
        "avideo_id"     => $platformID,
    );

    $response = json_decode(postVariables("https://wwbn.com/api/function.php", $data, false));

    if (isset($response->error) && $response->error == false) {
        $new_object_data = array();
        foreach ($object_data as $key => $obj) {
            $new_object_data[$key] = $obj; 
        }
        $new_object_data["keys"]->requestReset = true;
        $wwbnIndexModel->updateObjectData(json_encode($new_object_data));
    }
    echo json_encode($response); die();
    
}

function reIndex($object_data) {
    global $global, $configuration, $wwbnIndexModel, $platformID;
    $email = $configuration->getContactEmail();
    $title = $configuration->getWebSiteTitle();
    $data = array(
        "apiName"       => "reIndex",
        "avideo_id"     => $platformID,
        "engine_name"   => $title, //$object_data->engine_name
        "engine_logo"   => $global['webSiteRootURL'].$configuration->getLogo(),
        "engine_icon"   => $configuration->getFavicon(true),
        "feed_url"      => $global['webSiteRootURL']. "plugin/API/get.json.php?APIName=video&rowCount=20&search=[TERMS]",
        "detail_url"    => $global['webSiteRootURL']. "plugin/API/get.json.php?APIName=video&videos_id=[LID]",
        "email"         => $email, //$object_data->email 
        "acct_email"    => $object_data->email, 
        "host"          => parse_url($global['webSiteRootURL'])['host'],
    );
    
    $response = json_decode(postVariables("https://wwbn.com/api/function.php", $data, false));

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
        $update = $wwbnIndexModel->updateObjectData(json_encode($new_object_data));
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
    global $configuration, $platformID;
    $email = $configuration->getContactEmail();
    $data = array(
        "apiName"       => "unIndex",
        // "engine_name"   => $object_data->engine_name, 
        "avideo_id"     => $platformID,
        "email"         => $email, //$object_data->email 
        "acct_email"    => $object_data->email,  
    );
    return postVariables("https://wwbn.com/api/function.php", $data, false);
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
    global $wwbnIndexModel, $platformID;
    $object_data = $wwbnIndexModel->getPluginData()[0]['object_data'];
    if ($object_data == "" || $object_data == null) {
        return json_encode(array("error" => true, "title" => "Plugin data not Found", "message" => "Ops! Something went wrong. Please refresh the page and try again."));
    }
    $object_data = json_decode($object_data);
    if ($object_data == "" || $object_data == null) {
        return json_encode(array("error" => true, "title" => "Plugin data not Found", "message" => "Ops! Something went wrong. Please refresh the page and try again."));
    }
    $data = array(
        "apiName"   => "sendOTPCode",
        "email"     => $email,
        "avideo_id" => $platformID
    );
    return postVariables("https://wwbn.com/api/function.php", $data, false);
}

function verifyEmail($object_data, $otp) {
    global $platformID;
    $email = $object_data->email;
    $username = $object_data->username;
    $data = array(
        "apiName"       => "verifyEmail",
        "email"         => $email,
        "username"      => $username,
        "siteacctid"    => 4541, // WWBN account id
        "siteid_fk"     => 152, // WWBN
        "sitelinkid_fk" => 2504, // WWBN
        "avideo_id"     => $platformID,
        "otp"           => $otp
    );
    return postVariables("https://wwbn.com/api/function.php", $data, false);
}

function getYouPortalUser($object_data) {
    global $platformID;
    $data = array(
        "apiName"   => "getUser",
        "email"     => $object_data->email,
        "avideo_id" => $platformID
    );
    return json_decode(postVariables("https://wwbn.com/api/function.php", $data, false));
}

function getFeedStatus($host) {
    global $configuration, $platformID;
    $data = array(
        "apiName"       => "getFeedStatus",
        "engine_name"   => $configuration->getWebSiteTitle(), //$engine_name,
        "avideo_id"     => $platformID,
        "host"          => $host,
    );
    return json_decode(postVariables("https://wwbn.com/api/function.php", $data, false));
}

function exchangeKeys($plugin_data = null) {
    global $global, $configuration, $wwbnIndexModel, $platformID;

    if ($plugin_data == null) {
        $plugin_data = $wwbnIndexModel->getPluginData();
        $object_data = json_decode($plugin_data[0]['object_data']);
    } else {
        $object_data = json_decode($plugin_data[0]['object_data']);
    }

    $name = $configuration->getWebSiteTitle();
    $email = $configuration->getContactEmail();
    $UserIDPacket = "{$name} <{$email}>";

    $reset = false;
    $checkForResetOfKeys = checkForResetOfKeys();
    if (isset($checkForResetOfKeys->error) && $checkForResetOfKeys->error == false) {
        // check if need to generate new key
        if ($checkForResetOfKeys->title == "generate") {
            $reset = true;
        }
    } else {
        if ($checkForResetOfKeys->title == "pending") {
            return json_encode(array("error" => true, "title" => "Pending Request", "message" => "Your request for reset of keys is currently pending. Please wait before you can proceed or you can email us here to get an update (mail@wwbn.com)."));
        }
        return json_encode($checkForResetOfKeys);
    }

    if (!isset($object_data->keys) || $reset) {
        $keys = createKeys($UserIDPacket); // Generate Keys wwbnIndexCreateKeys
        $pub_key = $keys['public'];
        $priv_key = $keys['private'];
    } else {
        $pub_key = $object_data->keys->publicKey;
        $priv_key = $object_data->keys->privateKey;
    }

    $data = array(
        "apiName"           => "exchangeKeys",
        "name"              => $name,
        "pub_key"           => $pub_key,
        "email"             => $email,
        "avideo_id"         => $platformID,
        "host"              => parse_url($global['webSiteRootURL'])['host']
    );

    $response = json_decode(postVariables("https://wwbn.com/api/function.php", $data, false));
    
    if (isset($response->error) && $response->error == false) {

        $wwbn_pub_key = $response->wwbn_pub_key;

        $new_object_data = array();
        foreach ($object_data as $key => $obj) {
            $new_object_data[$key] = $obj; 
        }
        if (isset($new_object_data["keys"]->wwbnPublicKey) && $new_object_data["keys"]->wwbnPublicKey == $wwbn_pub_key) {
            return json_encode(array(
                "error" => false
            ));
        } else {
            $new_object_data["keys"]->publicKey = $pub_key;
            $new_object_data["keys"]->privateKey = $priv_key;
            $new_object_data["keys"]->wwbnPublicKey = $wwbn_pub_key;
            $new_object_data["keys"]->requestReset = false;
            $update = $wwbnIndexModel->updateObjectData(json_encode($new_object_data)); // save data to WWBNIndex plugin -> object_data
            if ($update) {
                $textToEncrypt = "Message from ". $name . " to encrypt";
                // Encrypt message using WWBN public key
                $textEncrypted = encryptMessage($textToEncrypt, $wwbn_pub_key); //wwbnIndexEncryptMessage
                if (empty($textEncrypted["encryptedMessage"])) { // Check if encryption failed  
                    return json_encode(array("error" => true, "title" => "Failed to encrypt", "message" => "Ops! Something wrong with WWBN public key. Please refresh the page and try again"));
                }

                $decrypt_avideo_result = decryptAVideoMessage($textEncrypted["encryptedMessage"]);
                if (isset($decrypt_avideo_result->error) && $decrypt_avideo_result->error == false) {
                    // Decrypt WWBN message
                    $wwbnEncryptedText = $decrypt_avideo_result->encrypted_text;

                    $textDecrypted = decryptMessage($wwbnEncryptedText, $priv_key); //wwbnIndexDecryptMessage
                    if (empty($textDecrypted)) {
                        return array("error" => true, "title" => "Failed to decrypt", "message" => "Ops! Something wrong with the WWBN encrypted text or with the AVideo private key. Please refresh the page and try again");
                    } 

                    // validation for avideo <-> wwbn is done
                    return json_encode(array(
                        "error" => false
                    ));

                } else {
                    return json_encode($decrypt_avideo_result);
                }
            } else {
                return array("error" => true, "title" => "Failed to update", "message" => "Ops! Something wrong updating plugin data. Please refresh the page and try again");
            }
        }
    }
    return json_encode($response);
}

function checkForResetOfKeys() {
    global $platformID;

    // Send encrypted message to WWBN
    $data = array(
        "apiName"           => "checkForResetOfKeys",
        "avideo_id"         => $platformID
    );

    return json_decode(postVariables("https://wwbn.com/api/function.php", $data, false));
}

function decryptAVideoMessage($textEncrypted) {
    global $platformID;

    // Send encrypted message to WWBN
    $data = array(
        "apiName"           => "decryptAVideoMessage",
        "encrypted_text"    => $textEncrypted,
        "avideo_id"         => $platformID
    );

    return json_decode(postVariables("https://wwbn.com/api/function.php", $data, false));
}


?>