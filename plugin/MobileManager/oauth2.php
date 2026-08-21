<?php
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';
require_once $global['systemRootPath'] . 'objects/user.php';

use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;

if (!empty($_GET['type'])) {
    if ($_GET['type'] === "Apple") {
        $obj = AVideoPlugin::getDataObjectIfEnabled('LoginApple');
        if (empty($obj)) {
            die('Apple Login is disabled');
        }
        $configOauth = [
            'callback' => HttpClient\Util::getCurrentUrl() . "?type={$_GET['type']}",
            'providers' => [
                $_GET['type'] => [
                    "enabled" => true,
                    "keys" => [
                        "id" => trim($obj->id),
                        "team_id" => trim($obj->team_id),
                        "key_id" => trim($obj->key_id),
                        "key_content" => trim($obj->key_content->value),
                    ],
                    "scope" => "name email",
                    "verifyTokenSignature" => true,
                ],
            ],
                /* optional : set debug mode
                  'debug_mode' => true,
                  // Path to file writable by the web server. Required if 'debug_mode' is not false
                  'debug_file' => __FILE__ . '.log', */
        ];
    } else {
        $login = AVideoPlugin::getLogin();
        foreach ($login as $value) {
            $obj = $value['loginObject']->getDataObject();
            if ($value['parameters']->type === $_GET['type']) {
                $id = $obj->id;
                $key = $obj->key;
                break;
            }
        }
        if (empty($id)) {
            die(sprintf(__("%s ERROR: You must set a ID on config"), $_GET['type']));
        }

        if (empty($key)) {
            die(sprintf(__("%s ERROR: You must set a KEY on config"), $_GET['type']));
        }

        $scope = 'email';
        if ($_GET['type'] === 'LinkedIn') {
            $scope = ['r_emailaddress'];
        }

        $configOauth = [
            'callback' => HttpClient\Util::getCurrentUrl() . "?type={$_GET['type']}",
            'providers' => [
                $_GET['type'] => [
                    'enabled' => true,
                    'keys' => ['id' => $id, 'secret' => $key, 'key' => $id],
                    "includeEmail" => true,
                    'scope' => $scope,
                    'trustForwarded' => false,
                ],
            ],
                /* optional : set debug mode
                  'debug_mode' => true,
                  // Path to file writable by the web server. Required if 'debug_mode' is not false
                  'debug_file' => __FILE__ . '.log', */
        ];
    }


    try {
        $hybridauth = new Hybridauth($configOauth);

        $adapter = $hybridauth->authenticate($_GET['type']);

        $tokens = $adapter->getAccessToken();
        $userProfile = $adapter->getUserProfile();

        //print_r($tokens);
        //print_r($userProfile);
        if (!empty($userProfile->email)) {
            $user = $userProfile->email;
        } else {
            $user = $userProfile->displayName;
        }
        $name = $userProfile->displayName;
        $photoURL = $userProfile->photoURL;
        $email = $userProfile->email;
        $pass = bin2hex(random_bytes(32)); // SECURITY: cryptographically random, never typed by the user
        $users_id = User::createUserIfNotExists($user, $pass, $name, $email, $photoURL);
        $userObject = new User($users_id);
        // Log in by user ID and keep credentials out of URLs/logs/history.
        $userObject->login(true);
        $adapter->disconnect();
        header("Location: oauth2Success.php");
        exit;
    } catch (\Exception $e) {
        header("Location: oauth2Error.php?message=" . $e->getMessage());
    }
}
