<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

if (!User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}");
}

$obj = AVideoPlugin::getObjectDataIfEnabled("YPTWallet");
$_page = new Page(array('Transfer Funds'));
?>
<style>
    .ui-menu .ui-menu-item {
        list-style-image: none !important;
        color: #EEE;
        font-weight: bolder;
    }

    /* Add visual feedback styles */
    .has-success {
        border-color: #5cb85c !important;
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 6px #67b168 !important;
    }

    .user-selected {
        background-color: #dff0d8;
        border-color: #5cb85c;
    }
</style>
<div class="container">
    <div class="row">
        <div class="row ">
            <div class="panel panel-default">
                <div class="panel-heading"><?php echo __("Transfer Funds"); ?></div>
                <div class="panel-body">
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
                                    <input type="number" name="value" id="value" placeholder="<?php echo __("Total Amount"); ?>"  class="form-control">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="users_name"><i class="fa fa-user"></i> <?php echo __("Transfer Funds to"); ?></label>
                                    <input type="text" id="users_name" placeholder="<?php echo __("Username or Email"); ?>" class="form-control">
                                    <input type="hidden" name="users_id" id="users_id">
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <?php
                                $capcha = getCaptcha();
                                echo $capcha['content'];
                                ?>
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
        </div>


    </div>
</div>

<div class="hidden" id="model">
    <div style="background-image: url('{background}'); background-size: cover; height: 50px; width: 100%;">
        <img src="{photo}" class="pull-left img img-responsive img-circle" style="max-height: 40px;" alt="User Photo">
        <div class="pull-left">
            {identification}
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#transferNow').click(function() {

            // Add validation before showing confirmation
            var usersId = $('#users_id').val();
            var users_name = $('#users_name').val();
            var value = $('#value').val();

            if (!usersId || usersId === '') {
                avideoAlertError(__("Please select a valid user to transfer funds to"));
                return false;
            }

            if (!users_name || users_name.trim() === '') {
                avideoAlertError(__("Please enter a valid username or email"));
                return false;
            }

            if (!value || parseFloat(value) <= 0) {
                avideoAlertError(__("Please enter a valid amount to transfer"));
                return false;
            }

            swal({
                title: __("Are you sure?"),
                text: __("You will transfer") + " " + value + " " + "<?php echo $obj->currency_symbol; ?>" + " " + __("to") + " " + users_name,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then(function(willDelete) {
                if (willDelete) {
                    modal.showPleaseWait();
                    $.ajax({
                        url: webSiteRootURL+'plugin/YPTWallet/view/transferFunds.json.php',
                        data: {
                            "value": $('#value').val(),
                            "users_id": $('#users_id').val(),
                            "captcha": <?php echo $capcha['captchaText']; ?>
                        },
                        type: 'post',
                        success: function(response) {
                            $(".walletBalance").text(response.walletBalance);
                            modal.hidePleaseWait();
                            if (response.error) {
                                setTimeout(function() {
                                    avideoAlertError(response.msg);
                                }, 500);
                            } else {
                                setTimeout(function() {
                                    avideoAlertSuccess(__("Funds successfully transferred"));
                                }, 500);
                            }
                        }
                    });
                }
            });
        });

        $("#users_name").autocomplete({
            minLength: 0,
            source: function(req, res) {
                $.ajax({
                    url: webSiteRootURL+'plugin/YPTWallet/view/users.json.php',
                    type: "POST",
                    data: {
                        searchPhrase: req.term
                    },
                    success: function(data) {
                        res(data.rows);
                    }
                });
            },
            focus: function(event, ui) {
                $("#users_name").val(ui.item.identification);
                return false;
            },
            select: function(event, ui) {
                $("#users_name").val(ui.item.identification);
                $("#users_id").val(ui.item.id);
                return false;
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            var html = $("#model").html();
            html = html.replace(new RegExp("{background}", 'g'), item.background);
            html = html.replace(new RegExp("{photo}", 'g'), item.photo);
            html = html.replace(new RegExp("{identification}", 'g'), item.identification);
            return $("<li>").append(html).appendTo(ul);
        };

        // Debug function to test autocomplete
        function testAutocomplete() {
            console.log("Testing autocomplete...");
            $.ajax({
                url: webSiteRootURL+'plugin/YPTWallet/view/users.json.php',
                type: "POST",
                data: {
                    searchPhrase: 'admin'
                },
                success: function(data) {
                    console.log("Autocomplete test response:", data);
                },
                error: function(xhr, status, error) {
                    console.log("Autocomplete test error:", error);
                }
            });
        }

        // Test autocomplete on page load
        testAutocomplete();
    });
</script>
<?php
$_page->print();
?>
