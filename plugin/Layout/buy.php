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

$paymentOptions = array();
$paymentPanel = array();

// PayPerView
$name = 'PayPerView';
if ($paymentOptions['ppv'] = AVideoPlugin::loadPluginIfEnabled($name)) {
    $obj = $paymentOptions['ppv']->getDataObject();
    $isVideoPPV = PayPerView::isVideoPayPerView($videos_id);
    if ($isVideoPPV) {
        $ppvplans = PayPerView::getAllPlansFromVideo($videos_id);
        $panel = array('title' => __('PPV Plans'), 'body' => array(), 'class' => 'success');
        foreach ($ppvplans  as $key => $value) {
            $sub_description = '';
            if (!empty($value['hours_valid'])) {
                $good_until = date('Y-m-d H:i:s', strtotime('+' . $value['hours_valid'] . ' hours'));
                $sub_description = __('Expires on') . ' ' . $good_until;
            }
            $panel['body'][] = array(
                'title' => $value['name'],
                'description' => $value['description'],
                'sub_description' => $sub_description,
                'price' => YPTWallet::formatCurrency($value['value']),
                'link' => "{$global['webSiteRootURL']}plugin/PayPerView/page/buy.php?videos_id={$videos_id}"
            );
        }
        $paymentPanel[] = $panel;
    }
}

// Subscription
$name = 'Subscription';
if ($paymentOptions['sub'] = AVideoPlugin::loadPluginIfEnabled($name)) {
    $subplans = $paymentOptions['sub']->getPlansFromVideo($videos_id);
    if (!empty($subplans)) {
        $panel = array('title' => __('Subscription'), 'body' => array(), 'class' => 'primary');
        foreach ($subplans  as $key => $value) {
            $plan = new SubscriptionPlansTable($value['subscriptions_plans_id']);
            $sub_description = '';
            if (!empty($plan->getPrice())) {
                $good_until = date("Y/m/d", strtotime("+{$plan->getHow_many_days()} days"));
                $sub_description = __('Auto renew on') . ' ' . $good_until;
            }
            $panel['body'][] = array(
                'title' => $plan->getName(),
                'description' => $plan->getDescription(),
                'sub_description' => $sub_description,
                'price' => YPTWallet::formatCurrency($plan->getPrice()),
                'link' => "{$global['webSiteRootURL']}plugin/Subscription/showPlans.php?videos_id={$videos_id}"
            );
        }
        $paymentPanel[] = $panel;
    }
}

$name = 'Gift';
if (!empty($ppvplans) && $paymentOptions['gift'] = AVideoPlugin::loadPluginIfEnabled($name)) {
    $panel = array('title' => __('Gifts'), 'body' => array(), 'class' => 'warning');
    foreach ($ppvplans  as $key => $value) {
        $sub_description = '';
        if (!empty($value['hours_valid'])) {
            $good_until = date('Y-m-d H:i:s', strtotime('+' . $value['hours_valid'] . ' hours'));
            $sub_description = __('Expires on') . ' ' . $good_until;
        }
        $panel['body'][] = array(
            'title' => $value['name'],
            'description' => $value['description'],
            'sub_description' => $sub_description,
            'price' => YPTWallet::formatCurrency($value['value']),
            'link' => "{$global['webSiteRootURL']}plugin/PayPerView/page/buy.php?videos_id={$videos_id}"
        );
    }
    $paymentPanel[] = $panel;
}

if (empty($paymentOptions)) {
    forbiddenPage('There is no purchase option');
}

$colSize = 12 / count($paymentOptions);

?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <div class="panel-title">
                <div class="row">
                    <div class="col-sm-6 col-md-4">
                        <img src="<?php echo $poster; ?>" class="img img-responsive img-rounded" />
                    </div>
                    <div class="col-sm-6 col-md-8">
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
            </div>
        </div>
        <div class="panel-body">
            <div class="row">

                <?php
                foreach ($paymentPanel as $key => $value) {
                ?>
                    <div class="col-md-<?php echo  $colSize; ?>">
                        <div class="panel panel-<?php echo  $value['class']; ?>">
                            <div class="panel-heading">
                                <h3 class="panel-title"><?php echo  $value['title']; ?></h3>
                            </div>
                            <div class="panel-body">
                                <?php
                                foreach ($value['body'] as $key2 => $value2) {
                                ?>
                                    <div class="text-center">
                                        <h2>
                                            <?php echo  $value2['title']; ?>
                                        </h2>
                                        <h3>
                                            <?php echo  $value2['price']; ?>
                                        </h3>
                                        <div>
                                            <?php echo  $value2['description']; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo  $value2['sub_description']; ?>
                                        </small>
                                        <hr>
                                        <div class="btn-group justified">
                                            <a class="btn navbar-btn btn-success" href="https://vlu.me/user?redirectUri=https%3A%2F%2Fvlu.me%2Fplugin%2FSubscription%2FshowPlans.php%3Fvideos_id%3D1284">
                                                <i class="fas fa-sign-in-alt"></i> Entrar
                                            </a>
                                            <a class="btn navbar-btn btn-default" href="https://vlu.me/signUp?redirectUri=https%3A%2F%2Fvlu.me%2Fplugin%2FSubscription%2FshowPlans.php%3Fvideos_id%3D1284">
                                                <i class="fas fa-user-plus"></i> Registrar-se
                                            </a>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    $_page->print();
    ?>