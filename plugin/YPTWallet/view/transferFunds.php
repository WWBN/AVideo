<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

if (!User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}");
}

$obj = YouPHPTubePlugin::getObjectDataIfEnabled("YPTWallet");
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title>Transfer Funds</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
        <style>
            .ui-menu .ui-menu-item {
                list-style-image: none !important;
                color: #EEE;
                font-weight: bolder;
            }
        </style>
    </head>

    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <div class="row bgWhite list-group-item">
                <div class="col-sm-6">
                    <?php echo $obj->transfer_funds_text ?>
                </div>
                <div class="col-sm-6">
                    <div class="row">

                        <div class="col-sm-12">
                            <?php
                            if (!empty($_GET['status'])) {
                                $text = "unknow";
                                $class = "danger";
                                switch ($_GET['status']) {
                                    case "fail":
                                        $text = $obj->transfer_funds_success_fail;
                                        break;
                                    case "success":
                                        $text = $obj->transfer_funds_success_success;
                                        $class = "success";
                                        break;
                                }
                                ?>
                                <div class="alert alert-<?php echo $class; ?>">
                                    <?php echo $text; ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="value"><?php echo __("Transfer Funds"); ?> <?php echo $obj->currency_symbol; ?> <?php echo $obj->currency; ?></label>
                                <input type="number" name="value" id="value" placeholder="<?php echo __("Total Amount"); ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="users_name"><i class="fa fa-user"></i> <?php echo __("Transfer Funds to"); ?></label>
                                <input type="text" id="users_name" placeholder="<?php echo __("Username or Email"); ?>">
                                <input type="hidden" name="users_id" id="users_id">
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="input-group">
                                <span class="input-group-addon"><img src="<?php echo $global['webSiteRootURL']; ?>captcha" id="captcha"></span>
                                <span class="input-group-addon"><span class="btn btn-xs btn-success" id="btnReloadCapcha"><span class="glyphicon glyphicon-refresh"></span></span></span>
                                <input name="captcha" placeholder="<?php echo __("Type the code"); ?>" class="form-control" type="text" style="height: 60px;" maxlength="5" id="captchaText">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <br>
                        <div class="col-sm-12 text-center">
                            <button class="btn btn-primary" id="transferNow"><i class="fa fa-exchange" aria-hidden="true"></i> <?php echo __("Transfer now"); ?></button>
                        </div>
                    </div>

                </div> 
            </div>
        </div>

        <div class="hidden" id="model">
            <div style="background-image: url('{background}'); background-size: cover; height: 50px; width: 100%;" >
                <img src="{photo}" class="pull-left img img-responsive img-circle" style="max-height: 40px;">
                <div class="pull-left">
                    {identification}
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
        <script>
            $(document).ready(function () {
                $('#btnReloadCapcha').click(function () {
                    $('#captcha').attr('src', '<?php echo $global['webSiteRootURL']; ?>captcha?' + Math.random());
                    $('#captchaText').val('');
                });

                $('#transferNow').click(function () {
                    swal({
                        title: "<?php echo __("Are you sure?"); ?>",
                        text: "<?php echo __("You will not be able to recover this action!"); ?>",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "<?php echo __("Yes, transfer it!"); ?>",
                        closeOnConfirm: true
                    },
                            function () {
                                modal.showPleaseWait();
                                $.ajax({
                                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/transferFunds.json.php',
                                    data: {
                                        "value": $('#value').val(),
                                        "users_id": $('#users_id').val(),
                                        "captcha": $('#captchaText').val()
                                    },
                                    type: 'post',
                                    success: function (response) {
                                        $(".walletBalance").text(response.walletBalance);
                                        modal.hidePleaseWait();
                                        if (response.error) {
                                            setTimeout(function () {
                                                swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                                            }, 500);
                                        } else {
                                            setTimeout(function () {
                                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Funds successfully transferred"); ?>", "success");
                                            }, 500);
                                        }
                                    }
                                });
                            });
                });

                $("#users_name").autocomplete({
                    minLength: 0,
                    source: function (req, res) {
                        $.ajax({
                            url: '<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/users.json.php',
                            type: "POST",
                            data: {
                                searchPhrase: req.term
                            },
                            success: function (data) {
                                res(data.rows);
                            }
                        });
                    },
                    focus: function (event, ui) {
                        $("#users_name").val(ui.item.identification);
                        return false;
                    },
                    select: function (event, ui) {
                        $("#users_name").val(ui.item.identification);
                        $("#users_id").val(ui.item.id);
                        return false;
                    }
                }).autocomplete("instance")._renderItem = function (ul, item) {
                    var html = $("#model").html();
                    html = html.replace(new RegExp("{background}", 'g'), item.background);
                    html = html.replace(new RegExp("{photo}", 'g'), item.photo);
                    html = html.replace(new RegExp("{identification}", 'g'), item.identification);
                    return $("<li>").append(html).appendTo(ul);
                };
            });
        </script>

    </body>
</html>
