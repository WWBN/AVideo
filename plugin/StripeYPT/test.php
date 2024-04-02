<?php
require_once '../../videos/configuration.php';

if (!User::isLogged()) {
    gotToLoginAndComeBackHere('Please login first');
}

$SubscriptionIsEnabled = AVideoPlugin::isEnabledByName("Subscription");
$_page = new Page(array("Stripe Subscription"));

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
            if (!empty($_REQUEST['payload'])) {
                $obj = StripeYPT::getMetadataOrFromSubscription(json_decode($_REQUEST['payload']));
                var_dump($obj);
            }
            ?>
        </div>
    </div>
</div>
<?php
$_page->print();
?>