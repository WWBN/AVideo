<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

if (!User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}

$users_id = 0;
if(!empty($_GET['users_id'])){
    $users_id = $_GET['users_id'];
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title>Support Author</title>
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
                <div class="panel-heading">History</div>
                <div class="panel-body">
                    <div class="row bgWhite list-group-item">
                        <table id="grid" class="table table-condensed table-hover table-striped">
                            <thead>
                                <tr>
                                    <th data-column-id="valueText"  data-width="150px"><?php echo __("Value"); ?></th>
                                    <th data-column-id="description" ><?php echo __("Description"); ?></th>
                                    <th data-column-id="created" data-order="desc" data-width="100px"><?php echo __("Date"); ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            $(document).ready(function () {

                var grid = $("#grid").bootgrid({
                    ajax: true,
                    url: "<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/log.json.php?users_id=<?php echo $users_id; ?>"
                });
            });

        </script>
    </body>
</html>
