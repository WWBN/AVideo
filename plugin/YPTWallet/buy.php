<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';
$title = array('Buy');
$global['doNotLoadPlayer'] = 1;

// get groups id
// get redirect URL
// get URL parameters


$videos_id = getVideos_id();
$users_groups_ids = array();
$parametersToAddInTheURL = array();
if (!empty($videos_id)) {
    $video = new Video('', '', $videos_id);
    $users_id = $video->getUsers_id();
    $title[] = $video->getTitle();
    $nameId = User::getNameIdentificationById($users_id);
    $poster = Video::getPoster($videos_id);
    $parametersToAddInTheURL['videos_id'] = $videos_id;
} else {
    $livet = LiveTransmition::getFromRequest();
    if (!empty($livet)) {
        if (!empty($livet["json"])) {
            $parametersToAddInTheURL['live_schedule'] = $livet["id"];
            if (!empty($livet["json"]["usergoups"])) {
                $users_groups_ids = $livet["json"]["usergoups"];
            }
        } else {
            $parametersToAddInTheURL['live_transmitions_id'] = $livet["id"];
            $lt = new LiveTransmition($livet["id"]);
            $users_groups_ids = $lt->getGroups();
        }

        if (User::userGroupsMatch($users_groups_ids)) {
            $link = Live::getLinkToLiveFromUsers_idAndLiveServer($livet['users_id'], $livet['live_servers_id'], $livet['live_index'], $livet['live_schedule_id']);
            //$redirectUri = getRedirectUri();
            header("Location: {$link}");
            exit;
        }
    }
}
//var_dump($users_groups_ids);exit;
$_page = new Page($title);
$_page->setExtraStyles(array('plugin/YPTWallet/buy.css'));
$paymentOptions = array();
$paymentPanel = array();

$giftObj = AVideoPlugin::getDataObjectIfEnabled('Gift');

// PayPerView
$name = 'PayPerView';
if ($paymentOptions['ppv'] = AVideoPlugin::loadPluginIfEnabled($name)) {
    $obj = $paymentOptions['ppv']->getDataObject();
    if (!empty($videos_id)) {
        $isVideoPPV = PayPerView::isVideoPayPerView($videos_id);
    }
    if (!empty($videos_id) && isset($isVideoPPV)) {
        $ppvplans = PayPerView::getAllPlansFromVideo($videos_id);
    } else {
        if (!empty($users_groups_ids)) {
            $ppvplans = Ppv_plans_has_users_groups::getAllPlansFromUserGroups($users_groups_ids);
        } else {
            $ppvplans = PPV_Plans::getAllActive();
        }
    }
    if (!empty($ppvplans)) {
        $panel = array(
            'title' => '<i class="fas fa-ticket-alt"></i> ' . __('PPV Plans'),
            'body' => array(),
            'class' => 'primary'
        );
        foreach ($ppvplans  as $key => $value) {
            $sub_description = '';
            if (!empty($value['hours_valid'])) {
                $good_until = date('Y-m-d H:i:s', strtotime('+' . $value['hours_valid'] . ' hours'));
                $sub_description = __('Expires on') . ' ' . $good_until;
            }
            $link = PayPerView::getBuyURL();;
            foreach ($parametersToAddInTheURL as $key => $pvalue) {
                $link = addQueryStringParameter($link, $key, $pvalue);
            }
            $link = addQueryStringParameter($link, 'plans_id', $value['id']);
            $users_groups_ids = array();
            $rows = Ppv_plans_has_users_groups::getAllFromPPV($value['id']);
            foreach ($rows as $key => $row) {
                $users_groups_ids[] = $row['users_groups_id'];
            }
            $panel['body'][] = array(
                'title' => $value['name'],
                'description' => $value['description'],
                'sub_description' => $sub_description,
                'price' => $value['value'],
                'link' => $link,
                'plans_id' => $value['id'],
                'type' => 'p',
                'users_groups_ids' => $users_groups_ids,
                'userGroupsMatch' => User::userGroupsMatch($users_groups_ids)
            );
            $users_id = User::getId();
            $user_users_groups = UserGroups::getUserGroups($users_id);
            $ids = AVideoPlugin::getDynamicUserGroupsId($users_id);
            //var_dump($users_id, $user_users_groups, $ids, $users_groups_ids, User::userGroupsMatch($users_groups_ids));exit;
        }
        if (!empty($panel['body'])) {
            $paymentPanel[] = $panel;
        }
    } else {
        unset($paymentOptions['ppv']);
    }
} else {
    unset($paymentOptions['ppv']);
}
// Subscription
$name = 'Subscription';
if ($paymentOptions['sub'] = AVideoPlugin::loadPluginIfEnabled($name)) {
    $subplans = array();
    if (!empty($videos_id)) {
        $subplans = $paymentOptions['sub']->getPlansFromVideo($videos_id);
    } else {
        if (!empty($users_groups_ids)) {
            $subplans = Subscription::getPlansByUserGroups($users_groups_ids);
        } else {
            $subplans = SubscriptionPlansTable::getAllActive();
        }
        foreach ($subplans as $key => $value) {
            $subplans[$key]['subscriptions_plans_id'] = $value['id'];
        }
    }
    //var_dump($subplans);exit;
    if (!empty($subplans)) {
        $panel = array(
            'title' => '<i class="fas fa-infinity"></i> ' . __('Subscription'),
            'body' => array(),
            'class' => 'primary'
        );
        foreach ($subplans  as $key => $value) {
            $plan = new SubscriptionPlansTable($value['subscriptions_plans_id']);
            $sub_description = '';
            if (!empty($plan->getPrice())) {
                $good_until = date("Y/m/d", strtotime("+{$plan->getHow_many_days()} days"));
                $sub_description = __('Auto renew on') . ' ' . $good_until;
            }
            $link = Subscription::getBuyURL();
            foreach ($parametersToAddInTheURL as $key => $pvalue) {
                $link = addQueryStringParameter($link, $key, $pvalue);
            }
            $link = addQueryStringParameter($link, 'plans_id', $value['subscriptions_plans_id']);
            $users_groups_ids = array();
            $rows = SubscriptionPlansGroupsTable::getAllFromPlan($value['subscriptions_plans_id']);
            foreach ($rows as $key => $row) {
                $users_groups_ids[] = $row['users_groups_id'];
            }
            $panel['body'][] = array(
                'title' => $plan->getName(),
                'description' => $plan->getDescription(),
                'sub_description' => $sub_description,
                'price' => $plan->getPrice(),
                'link' => $link,
                'plans_id' => $value['subscriptions_plans_id'],
                'type' => 's',
                'users_groups_ids' => $users_groups_ids,
                'userGroupsMatch' => User::userGroupsMatch($users_groups_ids)
            );
        }
        if (!empty($panel['body'])) {
            $paymentPanel[] = $panel;
        }
    } else {
        unset($paymentOptions['sub']);
    }
} else {
    unset($paymentOptions['sub']);
}

if (empty($paymentPanel)) {
    forbiddenPage('There is no payment option available for the selected content. Please choose other content or check back later');
}

$colSize = 12 / count($paymentOptions);

//var_dump($colSize, $paymentOptions);exit;
?>
<div class="container buy">
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <?php
            if ($poster) {
            ?>
                <div class="panel-title">
                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <img src="<?php echo $poster; ?>" class="img img-responsive img-rounded" />
                        </div>
                        <div class="col-sm-6 col-md-9">
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
            <?php
            }
            ?>
        </div>
        <div class="panel-body">
            <div class="row">

                <?php
                foreach ($paymentPanel as $key => $value) {
                ?>
                    <div class="col-md-<?php echo  $colSize; ?>">
                        <div class="panel panel-<?php echo  $value['class']; ?>">
                            <div class="panel-heading">
                                <h2 class="panel-title"><?php echo  $value['title']; ?></h2>
                            </div>
                            <div class="panel-body">
                                <?php
                                foreach ($value['body'] as $key2 => $value2) {
                                    $class = 'default';
                                    if ($value['userGroupsMatch']) {
                                        $class = 'success';
                                    }
                                ?>
                                    <div class="panel panel-<?php echo $class; ?>">
                                        <div class="panel-heading">
                                            <h3>
                                                <?php echo  $value2['title']; ?>
                                            </h3>
                                        </div>
                                        <div class="panel-body">
                                            <div class="text-center">
                                                <h3 class="price">
                                                    <?php echo YPTWallet::formatCurrency($value2['price']); ?>
                                                </h3>
                                                <div>
                                                    <?php echo  $value2['description']; ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo  $value2['sub_description']; ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="panel-footer">
                                            <div class="btn-group justified">
                                                <a class="btn navbar-btn btn-primary" href="<?php echo  $value2['link']; ?>">
                                                    <i class="fas fa-shopping-cart"></i> <?php echo __('Buy'); ?>
                                                </a>
                                                <?php
                                                if ($giftObj) {
                                                    $url = "{$global['webSiteRootURL']}plugin/Gift/View/createGift.php";
                                                    foreach ($parametersToAddInTheURL as $key => $pvalue) {
                                                        $url = addQueryStringParameter($url, $key, $pvalue);
                                                    }
                                                    $url = addQueryStringParameter($url, 'plans_id', $value2['plans_id']);
                                                    $url = addQueryStringParameter($url, 'type', $value2['type']);
                                                ?>
                                                    <button class="btn navbar-btn btn-warning" onclick="avideoModalIframeFullScreen('<?php echo $url; ?>');">
                                                        <i class="fas fa-gift"></i> <?php echo __('Buy as a Gift'); ?>
                                                    </button>
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
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>