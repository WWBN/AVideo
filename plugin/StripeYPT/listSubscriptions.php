<?php
require_once '../../videos/configuration.php';

if (!User::isLogged()) {
    gotToLoginAndComeBackHere('Please login first');
}

$filter_users_id = User::getId();

$stripe = AVideoPlugin::loadPlugin("StripeYPT");
if (User::isAdmin()) {
    $filter_users_id = 0;
    $subs = $stripe->getAllSubscriptions();
} else {
    $_REQUEST['users_id'] = $filter_users_id;
    $subs = $stripe->getAllSubscriptionsSearch($filter_users_id, 0);
}

if (!empty($_GET['subscription_tid'])) {
    $response = $stripe->cancelSubscriptions($_GET['subscription_tid']);
    _error_log('listSubscription::cancel subscritpion canceled ' . json_encode($response));
    if (!empty($_REQUEST['plans_id']) && !empty($_REQUEST['users_id'])) {
        $row = SubscriptionTable::getSubscription($_REQUEST['users_id'], $_REQUEST['plans_id']);
        if (!empty($response)) {
            SubscriptionTable::updateStripeCostumerId($row['id'], "canceled");
        }
    } else {
        _error_log('listSubscription::cancel plans_id or user not found');
    }
}

$SubscriptionIsEnabled = AVideoPlugin::isEnabledByName("Subscription");
$_page = new Page(array("Stripe Subscription"));
?>

<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">

            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#active">Active</a></li>
                <li><a data-toggle="tab" href="#trial">Trial</a></li>
            </ul>
        </div>
        <div class="panel-body">
            <div class="tab-content">
                <div id="active" class="tab-pane fade in active">
                    <div class="row">
                        <?php
                        $count = 0;
                        foreach ($subs->data as $value) {
                            $count++;
                            $users_id = $value->metadata->users_id;
                            $plans_id = $value->metadata->plans_id;
                            $message = array();
                            if (!empty($filter_users_id) && $filter_users_id != $users_id) {
                                continue;
                            }

                            $title = "";
                            $body = "";
                            $buttonClass = "danger";

                            if (!empty($users_id)) {
                                $user = new User($users_id);
                                if (!empty($user)) {
                                    $title .= $user->getNameIdentificationBd() . " (" . $user->getEmail() . ")";
                                }
                            } else {
                                $title .= "User ID Not found";
                            }
                            if ($SubscriptionIsEnabled) {
                                if (!empty($plans_id)) {
                                    $plan = new SubscriptionPlansTable($plans_id);
                                    if (!empty($plan)) {
                                        $title .= " [" . $plan->getName() . "]";
                                        $row = SubscriptionTable::getSubscription($users_id, $plans_id);
                                        if (!empty($row)) {
                                            $buttonClass = "success";
                                        } else {
                                            $message[] = "Could not find a subscription for user {$users_id},{$plans_id} ";
                                        }
                                    }
                                } else {
                                    $title .= " [Plan ID Not found]";
                                }
                            }
                            $body .= "<b>Created in:</b> " . date("Y-m-d", $value->created);
                            $body .= "<br><b>users_id:</b> " . $users_id;
                            $body .= "<br><b>plans_id:</b> " . $plans_id;
                            foreach ($value->items->data as $value2) {
                                $body .= "<br><b>Plan:</b> " . $value2->plan->nickname;
                                $body .= "<br><b>Value:</b> " . StripeYPT::addDot($value2->plan->amount) . " " . $value2->plan->currency;
                                $body .= "<br><b>Interval:</b> each " . $value2->plan->interval_count . " " . $value2->plan->interval;
                            }
                        ?>
                            <div class="col-sm-3">
                                <div class="panel panel-default">
                                    <div class="panel-heading"><?php echo $count; ?> <?php echo $title; ?></div>
                                    <div class="panel-body"><?php echo $body; ?></div>
                                    <div class="panel-footer">
                                        <a class="btn btn-sm btn-xs btn-<?php echo $buttonClass; ?> btn-block" href="<?php echo $global['webSiteRootURL']; ?>plugin/StripeYPT/listSubscriptions.php?subscription_tid=<?php echo $value->id; ?>&plans_id=<?php echo $plans_id; ?>&users_id=<?php echo $users_id; ?>">Cancel</a>
                                    </div>
                                    <div class="panel-footer">
                                        <?php
                                        foreach ($value->metadata as $key => $value) {
                                            echo "<b>{$key}</b>: " . json_encode($value) . "<br>";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php
                            if ($count % 4 === 0) {
                                echo '<div class="clearfix visible-sm visible-md visible-lg"></div>';
                            }
                        }
                        ?>
                    </div>
                </div>
                <div id="trial" class="tab-pane fade">
                    <div class="row">
                        <?php
                        $count = 0;
                        $subs = $stripe->getAllSubscriptions('trialing');
                        foreach ($subs->data as $value) {
                            $count++;
                            $users_id = $value->metadata->users_id;
                            $plans_id = $value->metadata->plans_id;

                            if (!empty($filter_users_id) && $filter_users_id != $users_id) {
                                continue;
                            }

                            $title = "";
                            $body = "";
                            $buttonClass = "danger";

                            if (!empty($users_id)) {
                                $user = new User($users_id);
                                if (!empty($user)) {
                                    $title .= $user->getNameIdentificationBd() . " (" . $user->getEmail() . ")";
                                }
                            } else {
                                $title .= "User ID Not found";
                            }
                            if ($SubscriptionIsEnabled) {
                                if (!empty($plans_id)) {
                                    $plan = new SubscriptionPlansTable($plans_id);
                                    if (!empty($plan)) {
                                        $title .= " [" . $plan->getName() . "]";
                                        $row = SubscriptionTable::getSubscription($users_id, $plans_id);
                                        if (!empty($row)) {
                                            $buttonClass = "success";
                                        }
                                    }
                                } else {
                                    $title .= " [Plan ID Not found]";
                                }
                            }
                            $body .= "<b>Created in:</b> " . date("Y-m-d", $value->created);
                            foreach ($value->items->data as $value2) {
                                $body .= "<br><b>Plan:</b> " . $value2->plan->nickname;
                                $body .= "<br><b>Value:</b> " . StripeYPT::addDot($value2->plan->amount) . " " . $value2->plan->currency;
                                $body .= "<br><b>Interval:</b> each " . $value2->plan->interval_count . " " . $value2->plan->interval;
                            }
                        ?>
                            <div class="col-sm-3">
                                <div class="panel panel-default">
                                    <div class="panel-heading"><?php echo $count; ?> <?php echo $title; ?></div>
                                    <div class="panel-body"><?php echo $body; ?></div>
                                    <div class="panel-footer">
                                        <a class="btn btn-sm btn-xs btn-<?php echo $buttonClass; ?> btn-block" href="<?php echo $global['webSiteRootURL']; ?>plugin/StripeYPT/listSubscriptions.php?subscription_tid=<?php echo $value->id; ?>">Cancel</a>
                                    </div>
                                    <div class="panel-footer">
                                        <?php
                                        foreach ($value->metadata as $key => $value) {
                                            echo "<b>{$key}</b>: " . json_encode($value) . "<br>";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php
                            if ($count % 4 === 0) {
                                echo '<div class="clearfix visible-sm visible-md visible-lg"></div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer">

        </div>
    </div>
</div>
<?php
$_page->print();
?>