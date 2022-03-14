<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isLogged()) {
    forbiddenPage();
}
$rows = PayPalYPT::getAllLogsFromUser(User::getId());
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title>Paypal subscriptions</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">

            <table class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php echo __("Type"); ?></th>
                        <th><?php echo __("Agreement ID"); ?></th>
                        <th><?php echo __("Status"); ?></th>
                        <th><?php echo __("Date"); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($rows as $value) {
                        $json = json_decode($value['json']);
                        $value['status'] = '';
                        if (!empty($value['agreement_id'])) {
                            $agreement = PayPalYPT::getBillingAgreement($value['agreement_id']);
                            if (!empty($agreement)) {
                                $value['status'] = $agreement->getState();
                            }
                        } ?>
                        <tr id="tr<?php echo $value['agreement_id']; ?>">
                            <td><?php echo $value['id']; ?></td>
                            <td>
                                <?php echo $json->get->json->type; ?>
                            </td>
                            <td>
                                <?php echo $value['agreement_id']; ?>
                            </td>
                            <td>
                                <?php echo $value['created']; ?>
                            </td>
                            <td><?php echo $value['expiration']; ?></td>
                            <td><?php echo $value['expiration']; ?></td>
                            <td>
                                <?php
                                if ($value['status'] == 'Active') {
                                    ?>
                                    <button class="btn btn-danger btn-xs" onclick="cancelAgreement('<?php echo $value['agreement_id']; ?>')">
                                        <?php
                                        echo __('Cancel Agreement'); ?>
                                    </button>
                                    <?php
                                } ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th><?php echo __("Type"); ?></th>
                        <th><?php echo __("Agreement ID"); ?></th>
                        <th><?php echo __("Status"); ?></th>
                        <th><?php echo __("Date"); ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            $(document).ready(function () {

            });

            function cancelAgreement(agreement_id) {
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL + 'plugin/PayPalYPT/agreementCancel.json.php',
                    data: {agreement: agreement_id},
                    type: 'post',
                    success: function (response) {
                        if (response.error) {
                            avideoAlertError(response.msg);
                        } else {
                            $('.tr' + agreement_id).fadeOut();
                            avideoToastSuccess(response.msg);
                        }
                        modal.hidePleaseWait();
                    }
                });
            }
        </script>
    </body>
</html>
