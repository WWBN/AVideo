<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}

if(empty($_GET['type'])){
    forbiddenPage("Wrong Type");
}

if(!User::isLogged()){
    gotToLoginAndComeBackHere(__("Please login first"));
}

$iframeURL = "";

switch ($_GET['type']){
    case "upload":
        if ((isset($advancedCustomUser->onlyVerifiedEmailCanUpload) && $advancedCustomUser->onlyVerifiedEmailCanUpload && User::isVerified()) || (isset($advancedCustomUser->onlyVerifiedEmailCanUpload) && !$advancedCustomUser->onlyVerifiedEmailCanUpload) || !isset($advancedCustomUser->onlyVerifiedEmailCanUpload)) {
            if (!empty($config->getEncoderURL())) {
                $iframeURL = $config->getEncoderURL()."?noNavbar=1&".getCredentialsURL();
            }else{
                if(empty($iframeURL)){
                    forbiddenPage("Your encoder is empty");
                }
            }
        }else{
            if(empty($iframeURL)){
                forbiddenPage("You cannot upload 1");
            }
        }
        break;
    case "network":
        if ((isset($advancedCustomUser->onlyVerifiedEmailCanUpload) && $advancedCustomUser->onlyVerifiedEmailCanUpload && User::isVerified()) || (isset($advancedCustomUser->onlyVerifiedEmailCanUpload) && !$advancedCustomUser->onlyVerifiedEmailCanUpload) || !isset($advancedCustomUser->onlyVerifiedEmailCanUpload)) {
            if (!empty($advancedCustom->encoderNetwork) && empty($advancedCustom->doNotShowEncoderNetwork)) {
                $iframeURL = $advancedCustom->encoderNetwork."?".getCredentialsURL();
            }else{
                if(empty($iframeURL)){
                    forbiddenPage("Network is disabled");
                }
            }
        }else{
            if(empty($iframeURL)){
                forbiddenPage("You cannot upload 1");
            }
        }
        break;
    case "log":
        $iframeURL = $global['webSiteRootURL']."view/logs.php";
        break;
    
}
if(empty($iframeURL)){
    forbiddenPage("Invalid Type {$_GET['type']}");
}


?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <iframe src="<?php echo $iframeURL; ?>" style="width: 100%; height: calc( 100vh - 50px );">
            
        </iframe>
        <?php
        
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
