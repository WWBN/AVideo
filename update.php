<?php
//check if there is a update
if (!User::isAdmin() || !file_exists($global['systemRootPath'].'updateDb.sql')) {
    return false;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $global['webSiteTitle']; ?> :: <?php echo $video['title']; ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body>
        <?php
        include 'include/navbar.php';
        ?><div class="container-fluid">
            <?php
        if (!file_exists($global['systemRootPath'].'updateDb.sql')) {
            ?>
                <div class="alert alert-danger">
                    <?php echo __("Sorry, no update is available right now."); ?><br>
                </div>
            <?php
        }else 
        if (empty($_GET['update'])) {
            ?>
                <div class="alert alert-warning">
                    <?php echo __("This is an update from version 0 to version 1"); ?><br>
                </div>
                <div class="alert alert-warning">
                    this is to update version 0 to 1
                    <?php echo __("We detected a pending update, if you want to do it now click Continue, if you do not want to update, remove the updateDb.sql file"); ?><br>
                    <a class="btn btn-warning" href="?update=1" > <span class="glyphicon glyphicon-refresh"></span> <?php echo __("Continue"); ?> </a>
                </div>
                <?php
            } else {
                $obj = new stdClass();
                $templine = '';
                $lines = file("{$global['systemRootPath']}updateDb.sql");
                $obj->error = "";
                foreach ($lines as $line) {
                    if (substr($line, 0, 2) == '--' || $line == '')
                        continue;
                    $templine .= $line;
                    if (substr(trim($line), -1, 1) == ';') {
                        if (!$global['mysqli']->query($templine)) {
                            $obj->error = ('Error performing query \'<strong>' . $templine . '\': ' . $global['mysqli']->error . '<br /><br />');
                        }
                        $templine = '';
                    }
                }

                $sql = "DELETE FROM configurations WHERE id = 1 ";
                if ($global['mysqli']->query($sql) !== TRUE) {
                    $obj->error = "Error deleting user: " . $global['mysqli']->error;
                    echo json_encode($obj);
                    exit;
                }

                $sql = "INSERT INTO configurations (id, video_resolution, users_id, version,  created, modified) VALUES (1, '426:240', 1,'1.0', now(), now())";
                if ($global['mysqli']->query($sql) !== TRUE) {
                    $obj->error = "Error creating configuration user: " . $global['mysqli']->error;
                    echo json_encode($obj);
                    exit;
                }
                $renamed = rename("{$global['systemRootPath']}updateDb.sql", "{$global['systemRootPath']}updateDb.sql.old");

                ?>
                <div class="alert alert-success">
                    <?php 
                        if($renamed){
                            echo __("Your update is done, click continue"); 
                        }else{
                            echo __("Your update is done, remove the updateDb.sql file to continue"); 
                        }
                    ?><br>
                    <a class="btn btn-success" href="?done=1" > <span class="glyphicon glyphicon-ok"></span> <?php echo __("Continue"); ?> </a>
                </div>
                <?php
            }
            ?></div><?php
            include 'include/footer.php';
            ?>
        <script>
            $(document).ready(function () {
            });
        </script>
    </body>
</html>
<?php
exit;
?>