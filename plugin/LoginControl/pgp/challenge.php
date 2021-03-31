<?php
require_once dirname(__FILE__) . '/../../../videos/configuration.php';
AVideoPlugin::loadPlugin("LoginControl");
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo __("PGP Challenge"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <br>
        <div class="container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php echo __('Two-Factor Challenge'); ?>
                    <button class="btn btn-default btn-xs pull-right" onclick="avideoModalIframe(webSiteRootURL + 'plugin/LoginControl/pgp/keys.php')"><i class="fas fa-key"></i> <?php echo __('Generate Keys'); ?>/<?php echo __('Tools'); ?></button>
                    <button class="btn btn-default pull-right btn-xs" onclick="copyToClipboard($('#pgpChallenge').val());"><i class="fas fa-copy"></i> <?php echo __('Copy to clipboard') ?></button>
                </div>
                <div class="panel-body">
                    <textarea class="form-control" rows="10" id="pgpChallenge"><?php echo LoginControl::getChallenge(); ?></textarea>
                </div>
                <div class="panel-footer">
                    <?php echo __('Two-Factor Response'); ?>
                    <input type="text" class="form-control" id="pgpResponse" placeholder="<?php echo __('Enter Code'); ?>"/>
                    <button class="btn btn-block btn-primary" onclick="checkCode();"><?php echo __('Check Code') ?></button>
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            function checkCode() {
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL + 'plugin/LoginControl/pgp/verifyChallenge.json.php',
                    method: 'POST',
                    data: {
                        'response': $('#pgpResponse').val()
                    },
                    success: function (response) {
                        if (response.error) {
                            avideoAlertError(response.msg);
                            modal.hidePleaseWait();
                        } else {
                            avideoToastSuccess("Success");
                            location.reload();
                        }
                    }
                });
            }
        </script>
    </body>
</html>
