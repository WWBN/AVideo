<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager wallets"));
    exit;
}

$plugin = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
$obj = $plugin->getDataObject();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title>Support Author</title>
        <meta name="generator" content="YouPHPTube - A Free Youtube Clone Script" />
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container">

            <div class="panel panel-default">
                <div class="panel-heading">Total Site Balance: <b><?php echo YPTWallet::getTotalBalanceText(); ?></b></div>
                <div class="panel-body">

                    <table id="grid" class="table table-condensed table-hover table-striped">
                        <thead>
                            <tr>
                                <th data-column-id="user" data-formatter="user"><?php echo __("User"); ?></th>
                                <th data-column-id="name" data-order="desc"><?php echo __("Name"); ?></th>
                                <th data-column-id="email" ><?php echo __("E-mail"); ?></th>
                                <th data-column-id="balance" ><?php echo __("Balance"); ?></th>
                                <th data-column-id="commands" data-formatter="commands" data-sortable="false" data-width="100px"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

        </div>

        <div id="userFormModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?php echo __("Balance Form"); ?></h4>
                    </div>
                    <div class="modal-body">
                        <form class="form-compact"  id="updateUserForm" onsubmit="">
                            <input type="hidden" id="inputUserId"  >
                            <label for="inputUser" class="sr-only"><?php echo __("User"); ?></label>
                            <input type="text" id="inputUser" class="form-control first" placeholder="<?php echo __("User"); ?>" readonly required="required">
                            <label for="inputUserBalance" class="sr-only"><?php echo __("Balance"); ?></label>
                            <input type="number" id="inputUserBalance" class="form-control last" placeholder="<?php echo __("Balance"); ?>" autofocus required="required">

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
                        <button type="button" class="btn btn-primary" id="saveUserBtn"><?php echo __("Save changes"); ?></button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            $(document).ready(function () {

                var grid = $("#grid").bootgrid({
                    ajax: true,
                    url: "<?php echo $global['webSiteRootURL'] . "plugin/YPTWallet/view/users.json.php"; ?>",
                    formatters: {
                        "commands": function (column, row) {
                            var editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-toggle="tooltip" data-placement="left" title="Edit"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>'
                            var history = '<a href="<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/history.php?users_id=' + row.id + '" class="btn btn-default btn-xs command-history"   data-toggle="tooltip" data-placement="left" title="History""><span class="fa fa-history" aria-hidden="true"></span></a>';
                            //return editBtn + deleteBtn;
                            return editBtn + history;
                        },
                        "user": function (column, row) {
                            var photo = "<br><img src='" + row.photo + "' class='img img-responsive img-rounded img-thumbnail' style='max-width:50px;'/>";
                            return row.user + photo;
                        }
                    }
                }).on("loaded.rs.jquery.bootgrid", function ()
                {
                    /* Executes after data is loaded and rendered */
                    grid.find(".command-edit").on("click", function (e) {
                        var row_index = $(this).closest('tr').index();
                        var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                        console.log(row);

                        $('#inputUserId').val(row.id);
                        $('#inputUser').val(row.user);
                        $('#inputUserBalance').val(row.balance);

                        $('#userFormModal').modal();
                    });
                });

                $('#saveUserBtn').click(function (evt) {
                    $('#updateUserForm').submit();
                });

                $('#updateUserForm').submit(function (evt) {
                    evt.preventDefault();
                    modal.showPleaseWait();

                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/saveBalance.php',
                        data: {
                            "users_id": $('#inputUserId').val(),
                            "balance": $('#inputUserBalance').val()
                        },
                        type: 'post',
                        success: function (response) {
                            if (!response.error) {
                                $(".walletBalance").text(response.walletBalance);
                                $('#userFormModal').modal('hide');
                                $("#grid").bootgrid("reload");
                            } else {
                                swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user has NOT been saved!"); ?>", "error");
                            }
                            modal.hidePleaseWait();
                        }
                    });
                    return false;
                });

            });

        </script>
    </body>
</html>
