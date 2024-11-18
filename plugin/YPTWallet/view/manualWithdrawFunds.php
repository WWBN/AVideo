<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

if (!User::isLogged()) {
    gotToLoginAndComeBackHere();
}
$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$balance =  YPTWallet::getUserBalance();
$obj = $plugin->getDataObject();
$_options = _json_decode($obj->withdrawFundsOptions);
$options = array();
foreach ($_options as $key => $value) {
    if ($value < $balance) {
        $options[] = $value;
    }
}
$options[] = $balance;

$withdrawFundsSiteCutPercentage = isset($obj->withdrawFundsSiteCutPercentage->value) ? floatval($obj->withdrawFundsSiteCutPercentage->value) : 0;

$_page = new Page(array('Withdraw Funds'));
?>
<div class="container">
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php echo __("Withdraw Funds"); ?>
                <?php
                if ($obj->enableAutoWithdrawFundsPagePaypal) {
                ?>
                    <label class="label label-success pull-right"><i class="fab fa-paypal"></i> <?php echo __('Automatic Withdraw'); ?></label>
                <?php
                }
                ?>
            </div>
            <div class="panel-body">
                <div class="col-sm-6">
                    <?php echo $obj->withdraw_funds_text ?>
                    <?php echo AVideoPlugin::getWalletConfigurationHTML(User::getId(), $plugin, $obj); ?>
                </div>
                <div class="col-sm-6">
                    <?php
                    if (!empty($_GET['status'])) {
                        $text = "unknown";
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
                        <label for="value"><?php echo __("Specify Amount"); ?> <?php echo $obj->currency_symbol; ?> <?php echo $obj->currency; ?></label>
                        <select class="form-control" id="value">
                            <?php
                            foreach ($options as $value) {
                                // Calculate fee and final value
                                $fee = ($value * $withdrawFundsSiteCutPercentage) / 100;
                                $finalValue = $value - $fee;
                            ?>
                                <option value="<?php echo $value; ?>" data-fee="<?php echo $fee; ?>" data-final-value="<?php echo $finalValue; ?>">
                                    <?php
                                    echo YPTWallet::formatCurrency($value);
                                    ?>
                                    (
                                    <?php echo __("Fee:"); ?>
                                    <?php
                                    echo YPTWallet::formatCurrency($value);
                                    ?>
                                    , <?php echo __("Final:"); ?>
                                    <?php echo YPTWallet::formatCurrency($finalValue); ?>
                                    )
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="information"><?php echo __("Information"); ?></label>
                        <textarea class="form-control" id="information" name="information"></textarea>
                    </div>
                    <button class="btn btn-primary btn-block" id="manualWithdrawFundsPageButton">
                        <i class="fas fa-dollar-sign"></i>
                        <?php echo $obj->manualWithdrawFundsPageButton; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {

        $('#manualWithdrawFundsPageButton').click(function() {
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'plugin/YPTWallet/view/manualWithdrawFunds.json.php',
                type: "POST",
                data: {
                    value: $('#value').val(),
                    information: $('#information').val()
                },
                success: function(response) {
                    $(".walletBalance").text(response.walletBalance);
                    modal.hidePleaseWait();
                    if (response.error) {
                        setTimeout(function() {
                            avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                        }, 500);
                    } else {
                        setTimeout(function() {
                            avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your request was sent"); ?>", "success");
                        }, 500);
                    }
                }
            });
        });
    });
</script>
<?php
$_page->print();
?>