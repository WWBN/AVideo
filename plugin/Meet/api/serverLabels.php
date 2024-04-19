<?php


if (!isset($_REQUEST['domain'])) {
    header("Access-Control-Allow-Origin: *");
} else {
    header("Access-Control-Allow-Origin: " . $_REQUEST['domain']);
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Origin");
    // header('P3P: CP="CAO PSA OUR"');
}
header("Content-Type: text/html");

if (!isset($global['systemRootPath'])) {
    $configFile = '../../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}

$obj = AVideoPlugin::getObjectDataIfEnabled("Meet");
//_error_log(json_encode($_SERVER));
if (empty($obj)) {
    die("Plugin disabled");
}

$serverStatus = Meet::getMeetServerStatus();
$moreJibris = "https://upgrade." . Meet::getServer()['domain'] . "/?webSiteRootURL=" . urlencode($global['webSiteRootURL']) . "&secret=" . Meet::getSecret();
$moreJibris = "#";
$moreJibrisOnclick = "avideoAlert('Comming soon');return false;";
if (User::isAdmin() && empty($serverStatus->error)) {
    ?>
    <span class="label label-primary" data-toggle="tooltip" data-placement="bottom" title="Unlimited number of meetings"><i class="fa fa-comments"></i> <span class="hidden-sm hidden-xs"><?php echo __("Unlimited"); ?></span></span>
    <span class="label label-primary" data-toggle="tooltip" data-placement="bottom" title="No limit on meeting legth limit on group meetings"><i class="fa fa-hourglass-start"></i>  <span class="hidden-sm hidden-xs"><?php echo __("Unlimited"); ?></span></span>
    <span class="label label-primary" data-toggle="tooltip" data-placement="bottom" title="You can have <?php echo empty($serverStatus->MUC_MAX_OCCUPANTS) ? "unlimited" : "up to " . $serverStatus->MUC_MAX_OCCUPANTS; ?> participants in each room"><i class="fa fa-users"></i>  <span class="hidden-sm hidden-xs"><?php echo empty($serverStatus->MUC_MAX_OCCUPANTS) ? __("Unlimited") : $serverStatus->MUC_MAX_OCCUPANTS; ?></span></span>
    <?php
    if (!empty($serverStatus->jibrisInfo->jibris)) {
        ?>
        <span class="label label-primary">
            <i class="fa fa-hourglass-start" data-toggle="tooltip" data-placement="bottom" title="You can transmit your meetings live <?php echo empty($serverStatus->JIBRI_USAGE_TIMEOUT) ? "unlimited" : "up to " . $serverStatus->JIBRI_USAGE_TIMEOUT; ?> Minutes"></i>
            <i class="fa fa-video-o"  data-toggle="tooltip" data-placement="bottom" title="You have <?php echo $serverStatus->jibris; ?> streaming services"></i> &nbsp;
            <?php
            foreach ($serverStatus->jibrisInfo->jibris as $jibriObj) {
                if ($jibriObj->isOnline) {
                    ?>
                    <i class="fa fa-circle-o-notch fa-spin" data-toggle="tooltip" data-placement="bottom" title="Instance <?php echo $jibriObj->instance; ?> is busy"></i>
                    <?php
                } else {
                    ?>
                    <i class="fa fa-circle-o-notch" data-toggle="tooltip" data-placement="bottom" title="Instance <?php echo $jibriObj->instance; ?> is available"></i>
                    <?php
                }
            }
            ?>
            &nbsp; <a class="fa fa-plus" data-toggle="tooltip" data-placement="bottom" title="Get more streaming services" href="<?php echo $moreJibris; ?>" style="color: white;" onclick="<?php echo $moreJibrisOnclick; ?>"></a>
        </span>
        <?php
    } else {
        ?>
        <a target="_blank" class="label label-warning" href="<?php echo $moreJibris; ?>"  onclick="<?php echo $moreJibrisOnclick; ?>">
            <i class="fa fa-video-camera" data-toggle="tooltip" data-placement="bottom" title="You do not have any streaming services, purchase one here"></i>
        </a>
        <?php
    }
}
?>
<span class="label label-<?php echo $serverStatus->error ? "danger" : ($serverStatus->isInstalled ? "success" : "warning") ?>" >
    <span data-toggle="tooltip" data-placement="bottom" title="<?php echo User::isAdmin() ? $serverStatus->msg : "Meet Server Status"; ?>">
        <?php echo ($serverStatus->error || !$serverStatus->isInstalled) ? "<i class=\"fa fa-exclamation-triangle\"></i>" : "<i class=\"fa fa-check-square\"></i>" ?>
        <?php echo ($serverStatus->error || !$serverStatus->isInstalled) ? "offline" : "online" ?>
        <span class="hidden-sm hidden-xs">(<?php
            echo Meet::getServer()['name'];
            ?>)
        </span>
    </span> 
    <a class="fa fa-random" data-toggle="tooltip" data-placement="bottom" title="Change Server" href="<?= $_REQUEST['changeServerURL'] ?>" style="color: white;"></a>
</span>
<?php
if (!empty($serverStatus->nextUpdate)) {
    ?>
    <script>
        $(document).ready(function () {
            setTimeout(function () {
                serverLabels()
            }, <?php echo $serverStatus->nextUpdate * 1000; ?> + serverLabelsStartTime);

            var si = 100;
            var counterBack = setInterval(function () {
                si -=<?php echo number_format(100 / ($serverStatus->nextUpdate), 2, ".", ""); ?>;
                if (si > 0) {
                    $('#serverProgressBar .progress-bar').css('width', si + '%');
                } else {
                    clearInterval(counterBack);
                    $('#serverProgressBar .progress-bar').css('width', '100%');
                    serverLabels();
                }

            }, 1000);
        });
    </script>
    <?php
} else {
    ?>
    <script>
        $(document).ready(function () {
        });
    </script>
    <?php
}
?>