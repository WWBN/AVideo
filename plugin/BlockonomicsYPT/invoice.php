<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}

$plugin = YouPHPTubePlugin::loadPluginIfEnabled("BlockonomicsYPT");
$obj = YouPHPTubePlugin::getObjectData("BlockonomicsYPT");
$order_id = $plugin->setUpPayment($_GET['value']);
$order = new BlockonomicsOrder($order_id);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Audit</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>


        <div class="container">

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h1>
                        Order#: <?php echo sprintf('%08d', $order->getId()); ?>
                    </h1>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h2>Bitcoin Address</h2>
                            <a href="bitcoin:<?php echo $order->getAddr(); ?>?amount=<?php echo $order->getFormatedBits(); ?>">
                                <div id="qrcode" class="text-center"></div>
                            </a>
                            <br/>
                            <div class="field">
                                <div class="control">
                                    <input type="text" class="input form-control" value="<?php echo $order->getAddr(); ?>" readonly="readonly">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h2>To pay, send exact amount of BTC to the given address</h2>
                            <h3>Amount</h3>
                            <p><strong><?php echo $order->getFormatedBits(); ?></strong> BTC ⇌ <strong><?php echo $order->getTotal_value(); ?></strong> <?php echo $order->getCurrency(); ?></p>

                            <br/>

                            <h2>Payment Details: </h2>

                                <h4 style="display: none;" class="bstatus label label-danger" id="status-3"> Payment Expired</h4>
                                <h4 style="display: none;" class="bstatus label label-danger" id="status-2"> Payment Error</h4>
                                <h4 style="display: none;" class="bstatus label label-warning" id="status0"> Unconfirmed</h4>
                                <h4 style="display: none;" class="bstatus label label-warning" id="status1"> Partially Confirmed</h4>
                                <h4 style="display: none;" class="bstatus label label-success" id="status2" >Confirmed</h4>

                            
                            <div>
                                Received : <strong id="received"><?php echo $order->getFormatedBits_payed(); ?></strong>
                                <small>BTC</small> 
                            </div>
                            <div style="margin-bottom:10px;" >
                                Transaction : <span id="transaction"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped active" role="progressbar"
                                 aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width:100%" id="timeleft">
                                100%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>plugin/BlockonomicsYPT/jquery.qrcode.min.js" type="text/javascript"></script>
        <script>
                                    var totalSeconds = <?php echo $obj->ExpireInSeconds; ?>;
                                    var totalSecondsPassed = <?php echo time() - strtotime($order->getCreated()); ?>;
                                    var totalSecondsleft = totalSeconds - totalSecondsPassed;
                                    $(document).ready(function () {
                                        $('#qrcode').qrcode({width: 220, height: 220, text: "bitcoin:<?php echo $order->getAddr(); ?>?amount=<?php echo $order->getFormatedBits(); ?>"});

                                        setInterval(function () {
                                            totalSecondsleft--;
                                            if (totalSecondsleft < 1) {
                                                totalSecondsleft = 0;
                                            }
                                            var percent = (totalSecondsleft / totalSeconds) * 100;

                                            $("#timeleft").css('width', percent + "%").html(totalSecondsleft + " <?php echo __("Seconds Left"); ?>");
                                        }, 1000);
                                        check();
                                    });

                                    function check() {
                                        $.ajax({
                                            url: '<?php echo $global['webSiteRootURL']; ?>plugin/BlockonomicsYPT/check.php?addr=<?php echo $order->getAddr(); ?>',
                                                        success: function (response) {
                                                            console.log(response);
                                                            if (response.status < 2) {
                                                                $("#transaction").html('<a target="_blank" href="http://www.blockonomics.co/api/tx?txid=' + response.txid + '&addr={{<?php echo $order->getAddr(); ?>}}">' + response.txid + '</a>');
                                                                $("#received").html((response.bits_payed / 1.0e8));
                                                                setTimeout(function () {
                                                                    check();
                                                                }, 3000);
                                                            }else{
                                                                response.status = 2;
                                                            }
                                                            $(".bstatus").not("#status"+response.status).hide();
                                                            $("#status"+response.status).fadeIn();
                                                        }
                                                    });
                                                }
        </script>
    </body>
</html>
