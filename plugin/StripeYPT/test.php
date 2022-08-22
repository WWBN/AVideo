<?php
require_once '../../videos/configuration.php';

if(!User::isLogged()){
    gotToLoginAndComeBackHere('Please login first');
}

$SubscriptionIsEnabled = AVideoPlugin::isEnabledByName("Subscription");

?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">
    <head>
        <title><?php echo __("Stripe Subscription") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <div class="panel panel-default">
                <div class="panel-heading">
                </div>
                <div class="panel-body">
                    <form action="#" method="post">
                        <textarea name="payload" style="width: 100%; min-height: 500px;">
                            
                        </textarea>
                        <input type="submit">
                    </form>
                </div>
                <div class="panel-footer">
                    <?php
                    if(!empty($_REQUEST['payload'])){
                        $obj = StripeYPT::getMetadataOrFromSubscription(json_decode($_REQUEST['payload']));
                        var_dump($obj);
                    }
                    ?>
                </div>
            </div>



        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
