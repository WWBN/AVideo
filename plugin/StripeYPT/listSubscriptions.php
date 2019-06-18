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
        $subs = StripeYPT::getAllSubscriptions();
        var_dump($subs);
        ?>
            
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
