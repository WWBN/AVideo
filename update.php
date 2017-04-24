<?php
require_once 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
//check if there is a update
if (!User::isAdmin()) {
    return false;
}


require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?><div class="container-fluid">
            <div class="alert alert-success"><?php printf(__("You are running YouPHPTube version %s!"), $config->getVersion()); ?></div>
        <?php
        if (empty($_POST['updateFile'])) {
            $files1 = scandir($global['systemRootPath']);
            $updateFiles = array();
            foreach ($files1 as $value) {
                preg_match("/updateDb.v([0-9.]*).sql/", $value, $match);
                if (!empty($match)) {
                    if ($config->currentVersionLowerThen($match[1])) {
                        $updateFiles[] = array('filename' => $match[0], 'version' => $match[1]);
                    }
                }
            }
            if(!empty($updateFiles)){
            ?>
                <div class="alert alert-warning">
                    <form method="post" class="form-compact well form-horizontal" >
                        <fieldset>
                            <legend><?php echo __("Update YouPHPTube System"); ?></legend>
                            <label for="updateFile" class="sr-only"><?php echo __("Select the update"); ?></label>
                            <select class="selectpicker" data-width="fit" name="updateFile" id="updateFile" required autofocus>
                                <?php
                                foreach ($updateFiles as $value) {
                                    echo "<option value=\"{$value['filename']}\">Version {$value[version]}</option>";
                                }
                                ?>
                            </select>
                            <?php printf(__("We detected a total of %d pending updates, if you want to do it now click (Update Now) button"), count($updateFiles)); ?>
                            <hr>
                            <button type="submit" class="btn btn-warning btn-lg center-block " href="?update=1" > <span class="glyphicon glyphicon-refresh"></span> <?php echo __("Update Now"); ?> </button>
                        </fieldset>
                    </form>
                </div>
                <?php
            }else{
                ?>
                <div class="alert alert-success">
                    <h2><?php echo __("Your system is up to date"); ?></h2>
                </div>
                <?php
            }
            } else {
                $obj = new stdClass();
                $templine = '';
                $lines = file("{$global['systemRootPath']}{$_POST['updateFile']}");
                $obj->error = "";
                foreach ($lines as $line) {
                    if (substr($line, 0, 2) == '--' || $line == '')
                        continue;
                    $templine .= $line;
                    if (substr(trim($line), -1, 1) == ';') {
                        if (!$global['mysqli']->query($templine)) {
                            $obj->error = ('Error performing query \'<strong>' . $templine . '\': ' . $global['mysqli']->error . '<br /><br />');
                            echo json_encode($obj);
                            //exit;
                        }
                        $templine = '';
                    }
                }
                // insert configuration if is version 1.0
                if ($config->currentVersionLowerThen('1.0')) {
                    $sql = "DELETE FROM configurations WHERE id = 1 ";
                    if ($global['mysqli']->query($sql) !== TRUE) {
                        $obj->error = "Error deleting configuration: " . $global['mysqli']->error;
                        echo json_encode($obj);
                        exit;
                    }

                    $sql = "INSERT INTO configurations (id, video_resolution, users_id, version,  created, modified) VALUES (1, '426:240', ".User::getId().",'1.0', now(), now())";
                    if ($global['mysqli']->query($sql) !== TRUE) {
                        $obj->error = "Error creating configuration: " . $global['mysqli']->error;
                        echo json_encode($obj);
                        exit;
                    }
                }
                
                if ($config->currentVersionEqual('1.0')) {
                    $sql = "UPDATE configurations SET  users_id = ".User::getId().", version = '1.1', webSiteTitle = '{$global['webSiteTitle']}', language = '{$global['language']}', contactEmail = '{$global['contactEmail']}', modified = now() WHERE id = 1";
                    if ($global['mysqli']->query($sql) !== TRUE) {
                        $obj->error = "Error creating configuration: " . $global['mysqli']->error;
                        echo json_encode($obj);
                        exit;
                    }
                }

                //$renamed = rename("{$global['systemRootPath']}updateDb.sql", "{$global['systemRootPath']}updateDb.sql.old");
                ?>
                <div class="alert alert-success">
                    <?php
                    printf(__("Your update from file %s is done, click continue"), $_POST['updateFile']); 
                    ?><hr>
                    <a class="btn btn-success" href="?done=1" > <span class="glyphicon glyphicon-ok"></span> <?php echo __("Continue"); ?> </a>
                </div>
                <?php
            }
            ?></div><?php
        include $global['systemRootPath'] . 'view/include/footer.php';
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