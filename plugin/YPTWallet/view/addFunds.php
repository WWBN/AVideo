<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';


$plugin = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
$paypal = YouPHPTubePlugin::loadPluginIfEnabled("PayPalYPT");
$obj = $plugin->getDataObject();
if (!empty($paypal)) {
    $paypalObj = $paypal->getDataObject();
}
$options = json_decode($obj->addFundsOptions);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title>Add Funds</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <div class="row bgWhite list-group-item">
                <div class="col-sm-6">
                    <?php echo $obj->add_funds_text ?>
                </div>
                <div class="col-sm-6">
                    <?php
                    if (!empty($_GET['status'])) {
                        $text = "unknow";
                        $class = "danger";
                        switch ($_GET['status']) {
                            case "fail":
                                $text = $obj->add_funds_success_fail;
                                break;
                            case "success":
                                $text = $obj->add_funds_success_success;
                                $class = "success";
                                break;
                            case "cancel":
                                $text = $obj->add_funds_success_cancel;
                                $class = "warning";
                                break;
                        }
                        ?>
                        <div class="alert alert-<?php echo $class; ?>">
                            <?php echo $text; ?>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="form-group">
                        <label for="exampleInputEmail1"><?php echo __("Add Funds"); ?> <?php echo $obj->currency_symbol; ?> <?php echo $obj->currency; ?></label>
                        <select class="form-control" id="value" >
                            <?php
                            foreach ($options as $value) {
                                ?>
                                <option value="<?php echo $value; ?>"><?php echo $obj->currency_symbol; ?> <?php echo $value; ?> <?php echo $obj->currency; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <?php
                    $plugin->getAvailablePayments();
                    ?>
                </div> 
            </div>
        </div>


        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            $(document).ready(function () {

            });
        </script>

    </body>
</html>
