<?php
require_once dirname(__FILE__) . '/../../../videos/configuration.php';
if (isBot()) {
    die();
}
if (!User::isLogged()) {
    forbiddenPage('Please login first');
}
$users_id = intval(@$_REQUEST['users_id']);

if (empty($users_id)) {
    forbiddenPage('Users_id is empty');
}

$obj = AVideoPlugin::getDataObjectIfEnabled('YPTSocket');

if (empty($obj->enableCalls)) {
    forbiddenPage('Calls are disabled on YPTSocket plugin');
}

$response = pluginsRequired(array('WebRTC', 'YPTSocket'), "Caller");

if ($response->error) {
    forbiddenPage($response->msg);
}

$identification = User::getNameIdentificationById($users_id);
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">

<head>
    <title><?php echo __("Caller") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
    <?php
    include $global['systemRootPath'] . 'view/include/head.php';
    ?>
    <link href="<?php echo getURL('view/css/custom/cyborg.css'); ?>" rel="stylesheet" type="text/css" />
    <style>
        body {
            text-align: center;
        }

        .calling .hideCalling,
        .showCalling {
            display: none;
        }

        .calling .showCalling {
            display: block;
        }

        .callerUserOffline .showCalling,
        .callerUserOffline .hideCalling {
            display: none;
        }

        .callerUserOffline .container-fluid {
            opacity: 0.5;
        }

        .callerUserOffline .userImage,
        .notCalling .userImage {
            animation: none;
        }

        #mainFooter {
            display: none !important;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <center>
            <img src="<?php echo User::getPhoto($users_id); ?>" class="img img-responsive img-circle userImage glowBox" style="height: 200px; width: 200px;">
            <h1><?php echo $identification; ?></h1>
            <div class="clearfix"></div>
            <div class="showCalling">
                <button class="btn btn-danger btn-lg" onclick="hangUpUserNow();"><i class="fas fa-phone-slash faa-ring animated"></i></button>
            </div>
            <div class="hideCalling">
                <button class="btn btn-success btn-lg" onclick="callUserNow();"><i class="fas fa-phone"></i></button>
            </div>
        </center>
    </div>
    <?php
    include $global['systemRootPath'] . 'view/include/footer.php';
    ?>
    <script src="<?php echo getURL('plugin/WebRTC/call/caller.js'); ?>" type="text/javascript"></script>
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                callUserNow();
            }, 1000);

        });

        function callUserNow() {
            callNow(<?php echo $users_id; ?>, <?php echo json_encode($identification); ?>);
        }

        function hangUpUserNow() {
            var json = getCallJsonFromUser(<?php echo $users_id; ?>, <?php echo json_encode($identification); ?>);
            hangUpCall(json);
            sendSocketMessageToUser(json, 'hangUpCall', <?php echo $users_id; ?>);
        }
        setInterval(function() {
            if (!isUserOnline(<?php echo $users_id; ?>)) {
                $('body').addClass('callerUserOffline');
            } else {
                $('body').removeClass('callerUserOffline');
            }
        }, 1000);
    </script>
</body>

</html>