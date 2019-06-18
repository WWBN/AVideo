<?php
require_once '../../videos/configuration.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage this plugin"));
    exit;
}
$stripe = YouPHPTubePlugin::loadPlugin("StripeYPT");
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
        $subs = $stripe->getAllSubscriptions();
        ?>
        <div class="container">
            <div class="row">  
                <?php
                foreach ($subs->data as $value) {
                    $users_id = $value->metadata->users_id;
                    $plans_id = $value->metadata->plans_id;

                    $title = "";
                    $body = "";

                    if (!empty($users_id)) {
                        $user = new User($users_id);
                        if (!empty($user)) {
                            $title .= $user->getEmail();
                        }
                    } else {
                        $title .= "User ID Not found";
                    }
                    if (!empty($plans_id)) {
                        $plan = new SubscriptionPlansTable($plans_id);
                        if (!empty($plan)) {
                            $title .= " [" . $plan->getName() . "]";
                        }
                    } else {
                        $title .= " [Plan ID Not found]";
                    }
                    $body .= "<b>Created in:</b> " . date("Y-m-d", $value->created);
                    foreach ($value->items->data as $value2) {
                        $body .= "<br><b>Plan:</b> " . $value2->plan->nickname;
                        $body .= "<br><b>Value:</b> " . StripeYPT::addDot($value2->plan->amount) . " " . $value2->plan->currency;
                        $body .= "<br><b>Interval:</b> each " . $value2->plan->interval_count . " " . $value2->plan->interval;
                    }
                    ?>
                    <div class="panel panel-default col-sm-4">
                        <div class="panel-heading"><?php echo $title; ?> <button class="btn btn-sm btn-xs btn-danger pull-right" onclick="cancel('<?php echo $value->id; ?>')" >Cancel</button></div>
                        <div class="panel-body"><?php echo $body; ?></div>
                    </div>    
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
