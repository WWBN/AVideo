<?php
global $global, $config;
$global['isIframe'] = 1;
// is online
// recorder
// live users

$global['ignoreUserMustBeLoggedIn'] = 1;
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    require_once $configFile;
}

if (!empty($_REQUEST['logoff'])) {
    User::logoff();
}
$html = '';
User::loginFromRequestIfNotLogged();
if (User::isLogged()) {
    //$html .= getIncludeFileContent($global['systemRootPath'] . 'plugin/MobileYPT/userButtons.php');
} else {
    if (!empty($_REQUEST['SignUp'])) {
        $html .= getIncludeFileContent($global['systemRootPath'] . 'view/userSignUpBody.php');
    } else {
        $redirectUri = "{$global['webSiteRootURL']}plugin/MobileYPT/loginPage.php";
        if (empty($signUpURL)) {
            $signUpURL = addQueryStringParameter($redirectUri, 'SignUp', 1);
        }
        $html .= getIncludeFileContent($global['systemRootPath'] . 'view/userLogin.php', array('signUpURL' => $signUpURL, '_GET[\'redirectUri\']' => $redirectUri, 'hideRememberMe' => 1));
    }
}

?>
<!DOCTYPE html>
<html lang="">
    <head>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            #accessibility-toolbar, footer, #socket_info_container{
                display: none !important;
            }
            body {
                padding: 0;
            }

            .liveUsersLabel{
                position: fixed;
                top: 10px !important;
            }
            .liveUsersLabel{
                left: 20px !important;
            }
            #recorderToEncoderActionButtons{
                position: absolute;
                top: 40px;
                left: 0;
                width: 100%;
            }
            .showWhenClosed, #closeRecorderButtons{
                display: none;
            }
            #recorderToEncoderActionButtons.closed .recordLiveControlsDiv,
            #recorderToEncoderActionButtons.closed .hideWhenClosed{
                display: none !important;
            }
            #recorderToEncoderActionButtons.closed .showWhenClosed,
            .isLiveOnline #closeRecorderButtons{
                display: inline-block !important;
            }
        </style>
    </head>

    <body style="background-color: transparent; <?php echo @$bodyClass; ?>">
        <?php
        echo $html;
        ?>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>