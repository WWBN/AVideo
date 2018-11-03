<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

if(!User::isLogged()){
    header("Location: {$global['webSiteRootURL']}");
}

$plugin = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
$obj = $plugin->getDataObject();

$wallet = new Wallet(0);
$wallet->setUsers_id(User::getId());
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
            <div class="row ">
                <div class="panel panel-default">
                    <div class="panel-heading"><?php echo __("Configurations"); ?></div>
                    <div class="panel-body">
                        <form id="form">
                            <div class="form-group">
                                <label for="CryptoWallet"><?php echo $obj->CryptoWalletName; ?>:</label>
                                <input type="text" class="form-control" name="CryptoWallet" value="<?php echo $wallet->getCrypto_wallet_address(); ?>">
                            </div>
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> <?php echo __("Save"); ?></button>
                        </form> 
                    </div>
                </div>
            </div>
        </div>


        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            $(document).ready(function () {
                $("#form").submit(function (event) {
                    event.preventDefault();
                    modal.showPleaseWait();
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/saveConfiguration.php',
                        data: $("#form").serialize(),
                        type: 'post',
                        success: function (response) {
                            if(!response.error){
                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Configuration Saved"); ?>", "success");
                            }else{
                                swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                            }
                            modal.hidePleaseWait();
                            console.log(response);
                        }
                    });
                });
            });
        </script>

    </body>
</html>
