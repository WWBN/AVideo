<?php
$isMyChannel = false;
if (User::isLogged() && $user_id == User::getId()) {
    $isMyChannel = true;
}
$user = new User($user_id);
$_GET['channelName'] = $user->getChannelName();

$_POST['sort']['created'] = "DESC";

if (empty($_GET['current'])) {
    $_POST['current'] = 1;
} else {
    $_POST['current'] = $_GET['current'];
}
$current = $_POST['current'];
$rowCount = 25;
$_POST['rowCount'] = $rowCount;

$uploadedVideos = Video::getAllVideos("a", $user_id, !isToHidePrivateVideos());
$uploadedTotalVideos = Video::getTotalVideos("a", $user_id, !isToHidePrivateVideos());

$totalPages = ceil($uploadedTotalVideos / $rowCount);

unset($_POST['sort']);
unset($_POST['rowCount']);
unset($_POST['current']);

$get = array('channelName' => $_GET['channelName']);
$palyListsObj = YouPHPTubePlugin::getObjectDataIfEnabled('PlayLists');
?>
<!-- <?php var_dump($uploadedTotalVideos, $user_id, !isToHidePrivateVideos()); ?> -->
<div class="bgWhite list-group-item gallery clear clearfix" >
    <?php
    if (empty($advancedCustomUser->doNotShowTopBannerOnChannel)) {
        ?>
        <div class="row bg-info profileBg" style="background-image: url('<?php echo $global['webSiteRootURL'], $user->getBackgroundURL(), "?", @filectime($global['systemRootPath'] . $user->getBackgroundURL()); ?>')">
            <img src="<?php echo User::getPhoto($user_id); ?>" alt="<?php echo $user->_getName(); ?>" class="img img-responsive img-thumbnail" style="max-width: 100px;"/>
        </div>    
        <?php
    }
    ?>
    <div class="row"><div class="col-6 col-md-12">
            <h1 class="pull-left">
                <?php
                echo $user->getNameIdentificationBd();
                ?>
                <?php
                echo User::getEmailVerifiedIcon($user_id)
                ?></h1>
            <span class="pull-right">
                <?php
                echo Subscribe::getButton($user_id);
                ?>
            </span>
        </div></div>
    <div class="col-md-12">
        <?php echo nl2br($user->getAbout()); ?>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php
            if ($isMyChannel) {
                ?>
                <a href="<?php echo $global['webSiteRootURL']; ?>mvideos" class="btn btn-success ">
                    <span class="glyphicon glyphicon-film"></span>
                    <span class="glyphicon glyphicon-headphones"></span>
                    <?php echo __("My videos"); ?>
                </a>
                <?php
            } else {
                echo __("My videos");
            }
            echo YouPHPTubePlugin::getChannelButton();
            ?>
        </div>
        <div class="panel-body">
            <?php
            if (!empty($uploadedVideos[0])) {
                $video = $uploadedVideos[0];
                $obj = new stdClass();
                $obj->BigVideo = true;
                $obj->Description = false;
                include $global['systemRootPath'] . 'plugin/Gallery/view/BigVideo.php';
                unset($uploadedVideos[0]);
            }
            ?>
            <div class="row mainArea">
                <?php
                createGallerySection($uploadedVideos, "", $get);
                ?>
            </div>
        </div>

        <div class="panel-footer">
            <ul id="channelPagging"></ul>
            <script>
                $(document).ready(function () {
                    $('#channelPagging').bootpag({
                        total: <?php echo $totalPages; ?>,
                        page: <?php echo $current; ?>,
                        maxVisible: 10
                    }).on('page', function (event, num) {
                        document.location = ("<?php echo $global['webSiteRootURL']; ?>channel/<?php echo $_GET['channelName']; ?>?current=" + num);
                    });
                });
            </script>
        </div>
    </div>
    <?php
    if (!empty($palyListsObj)) {
        include $global['systemRootPath'] . 'view/channelPlaylist.php';
    }
    ?>
</div>
<script src="<?php echo $global['webSiteRootURL']; ?>plugin/Gallery/script.js" type="text/javascript"></script>