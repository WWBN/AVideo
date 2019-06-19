<?php
require_once '../../videos/configuration.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage this plugin"));
    exit;
}
$stripe = YouPHPTubePlugin::loadPlugin("StripeYPT");

if (!empty($_GET['subscription_id'])) {
    $stripe->cancelSubscriptions($_GET['subscription_id']);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Stripe Subscription</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#active">Active</a></li>
                <li><a data-toggle="tab" href="#trial">Trial</a></li>
            </ul>

            <div class="tab-content">
                <div id="active" class="tab-pane fade in active">
                    <div class="row">   
                        <?php
                        $subs = $stripe->getAllSubscriptions();
                        foreach ($subs->data as $value) {
                            $users_id = $value->metadata->users_id;
                            $plans_id = $value->metadata->plans_id;

                            $title = "";
                            $body = "";
                            $buttonClass = "danger";

                            if (!empty($users_id)) {
                                $user = new User($users_id);
                                if (!empty($user)) {
                                    $title .= $user->getName() . " (" . $user->getEmail() . ")";
                                }
                            } else {
                                $title .= "User ID Not found";
                            }
                            if (YouPHPTubePlugin::isEnabledByName("Subscription")) {
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
                                    <div class="panel-heading"><?php echo $title; ?></div>
                                    <div class="panel-body"><?php echo $body; ?></div>
                                    <div class="panel-footer"> <a class="btn btn-sm btn-xs btn-<?php echo $buttonClass; ?> btn-block" href="<?php echo $global['webSiteRootURL']; ?>plugin/StripeYPT/listSubscriptions.php?subscription_id=<?php echo $value->id; ?>" >Cancel</a></div>
                                </div>
                            </div>    
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <div id="trial" class="tab-pane fade">
                    <div class="row">  
                        <?php
                        $subs = $stripe->getAllSubscriptions('trialing');
                        foreach ($subs->data as $value) {
                            $users_id = $value->metadata->users_id;
                            $plans_id = $value->metadata->plans_id;

                            $title = "";
                            $body = "";
                            $buttonClass = "danger";

                            if (!empty($users_id)) {
                                $user = new User($users_id);
                                if (!empty($user)) {
                                    $title .= $user->getName() . " (" . $user->getEmail() . ")";
                                }
                            } else {
                                $title .= "User ID Not found";
                            }
                            if (YouPHPTubePlugin::isEnabledByName("Subscription")) {
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
                                    <div class="panel-heading"><?php echo $title; ?></div>
                                    <div class="panel-body"><?php echo $body; ?></div>
                                    <div class="panel-footer"> <a class="btn btn-sm btn-xs btn-<?php echo $buttonClass; ?> btn-block" href="<?php echo $global['webSiteRootURL']; ?>plugin/StripeYPT/listSubscriptions.php?subscription_id=<?php echo $value->id; ?>" >Cancel</a></div>
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
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
