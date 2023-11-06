<?php

require_once '../../videos/configuration.php';

$_GET['avideoIframe'] = 1;
$obj = AVideoPlugin::getObjectDataIfEnabled("UserNotifications");
if (empty($obj)) {
    forbiddenPage(__("The plugin is disabled"));
    exit;
}

$_page = new Page(array('Notification'));

$_page->setExtraStyles(
    array(
        'plugin/UserNotifications/style.css',
    )
);
/*
$_page->setExtraStyles(
    array(
        'view/css/DataTables/datatables.min.css',
        'node_modules/video.js/dist/video-js.min.css'
    )
);
$_page->setExtraScripts(
    array(
        'node_modules/video.js/dist/video.min.js',
        'view/js/videojs-persistvolume/videojs.persistvolume.js',
        'view/js/BootstrapMenu.min.js',
        'view/css/DataTables/datatables.min.js',
    )
);
$users_id = User::getId();
$stats = getStatsNotifications();
$notifications = User_notifications::getAllForUsers_id($users_id);
*/
?>
<style>
    #topMenuUserNotifications>ul {
        display: block !important;
    }

    .hideWhenHasSomethingToShow {
        display: none !important;
    }

    #notifList>li,
    #topMenuUserNotifications>a {
        list-style-type: none;
        display: none;
    }

    #topMenuUserNotifications {
        display: list-item;
    }

    #topMenuUserNotifications>ul {
        max-width: 100vw;
    }

    #notifList {
        max-width: 100vw;
        width: 100vw;
        padding: 0;
        position: absolute;
        top: 0;
        left: 0;
    }

    #topMenuUserNotifications .dropdown-menu .list-group {
        max-height: calc(100vh - 80px);
    }
</style>
<div class="container-fluid">
    <ul id="notifList">
        <?php
        include $global['systemRootPath'] . 'plugin/UserNotifications/HTMLMenuRight.php';
        include $global['systemRootPath'] . 'plugin/Live/view/menuRight.php';
        //var_dump($stats);
        //var_dump($notifications);
        /*
        echo '<div class="list-group-item clearfix">';
        foreach ($stats["applications"] as $key => $value) {
            //echo $value["htmlExtraVideoListItem"];
        }
        echo '</div>';
        echo '<div class="clearfix"></div>';
        foreach ($notifications as $key => $value) {
            //echo UserNotifications::createTemplateFromArray($value);
        }
        */
        ?>
    </ul>
</div>
<?php
$_page->print();
?>