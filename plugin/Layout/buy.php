<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';
$videos_id = getVideos_id();
if (empty($videos_id)) {
    forbiddenPage('videos_id is required');
}
$video = new Video('', '', $videos_id);
$users_id = $video->getUsers_id();
if (User::isLogged()) {
    $response = $video->whyUserCannotWatchVideo(User::getId(), $videos_id);
    if ($response->canWatch) {
        header('Location: ' . Video::getURL($videos_id));
        exit;
    }
}
$title = array('Buy');
$title[] = $video->getTitle();
$nameId = User::getNameIdentificationById($users_id);
$poster = Video::getPoster($videos_id);
//User::getChannelLink()
$_page = new Page($title);
/*
$_page->setInlineStyles("body {
    background-image: url('{$poster}'); 
    background-size: cover;
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-position: center center;
}");

// FansSubscriptions
$name = 'FansSubscriptions';
if ($p = AVideoPlugin::loadPluginIfEnabled($name)) {
    $obj = $p->getDataObject();
    if (FansSubscriptions::isFansOnly($videos_id)) {
        
    }
}
*/
// PayPerView
$name = 'PayPerView';
if ($p = AVideoPlugin::loadPluginIfEnabled($name)) {
    $obj = $p->getDataObject();
    $isVideoPPV = PayPerView::isVideoPayPerView($videos_id);
    if ($isVideoPPV) {
        $ppvplans = PayPerView::getAllPlansFromVideo($videos_id);
    }
}
// Subscription
$name = 'Subscription';
if ($p = AVideoPlugin::loadPluginIfEnabled($name)) {
    $subplans = $p->getPlansFromVideo($videos_id);
}

?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <img src="<?php echo $poster; ?>" class="img img-responsive img-rounded pull-left" style="margin-right: 10px;" />
            <div class="panel-title pull-left">
                <h2>
                    <?php
                    echo $video->getTitle();
                    ?>
                </h2>
                <br>
                <a href="<?php echo User::getChannelLink($users_id); ?>" class="cleaarfix" data-toggle="tooltip" title="<?php echo $nameId; ?>">
                    <img src="<?php echo User::getPhoto($users_id); ?>" class="img img-responsive img-rounded pull-left channelPhoto" />
                    <?php echo $nameId; ?>
                </a>
            </div>
        </div>
        <div class="panel-body">

            <div class="row">


                <!-- Gift Options -->
                <div class="col-md-4">
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <h3 class="panel-title">Gift Options</h3>
                        </div>
                        <div class="panel-body">
                            <ul>
                                <li>Gift Option 1</li>
                                <li>Gift Option 2</li>
                                <li>Gift Option 3</li>
                                <!-- Add more gift options as needed -->
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- PPV Plans -->
                <div class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title">PPV Plans</h3>
                        </div>
                        <div class="panel-body">
                            <ul>
                                <li>PPV Plan 1</li>
                                <li>PPV Plan 2</li>
                                <li>PPV Plan 3</li>
                                <!-- Add more plans as needed -->
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Subscription Plans -->
                <div class="col-md-4">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h3 class="panel-title">Subscription Plans</h3>
                        </div>
                        <div class="panel-body">
                            <ul>
                                <li>Subscription Plan 1</li>
                                <li>Subscription Plan 2</li>
                                <li>Subscription Plan 3</li>
                                <!-- Add more plans as needed -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    $_page->print();
    ?>