<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
_session_start();
unset($_SESSION['sessionCache']['thereIsAnyRemoteUpdate']);
unset($_SESSION['sessionCache']['thereIsAnyUpdate']);
_session_write_close();
require_once $global['systemRootPath'] . 'objects/user.php';
//check if there is a update
if (!User::isAdmin()) {
    forbiddenPage("");
    exit;
}
adminSecurityCheck(true);
// remove cache dir before the script starts to let the script recreate the javascript and css files
if (!empty($_POST['updateFile'])) {
    $dir = Video::getStoragePath() . "cache";
    rrmdir($dir);
}
$_page = new Page(array('Update AVideo System'));
$_page->setExtraScripts(
    array(
        'view/js/three.js',
    )
);
?>
<style>
    html {
        height: unset;
    }

    body {
        background-color: #193c6d;
        filter: progid: DXImageTransform.Microsoft.gradient(gradientType=1, startColorstr='#003073', endColorstr='#029797');
        background-image: url(//img.alicdn.com/tps/TB1d.u8MXXXXXXuXFXXXXXXXXXX-1900-790.jpg);
        background-size: 100%;
        background-image: -webkit-gradient(linear, 0 0, 100% 100%, color-stop(0, #003073), color-stop(100%, #029797));
        background-image: -webkit-linear-gradient(135deg, #003073, #029797);
        background-image: -moz-linear-gradient(45deg, #003073, #029797);
        background-image: -ms-linear-gradient(45deg, #003073 0, #029797 100%);
        background-image: -o-linear-gradient(45deg, #003073, #029797);
        background-image: linear-gradient(135deg, #003073, #029797);
        margin: 0px;
        overflow: hidden;
        height: 100%;
    }

    .alert {
        text-align: center;
    }

    #updateInfo {
        position: absolute;
        top: 60px;
        z-index: 1;
        width: 500px;
        left: 50%;
        margin-left: -250px;
    }
</style>

<div class="container-fluid">
    <div id="updateInfo">
        <div>
            <img style="max-height: 20vh;
                        display: block;
                        margin-left: auto;
                        margin-right: auto;" src="https://youphp.tube/marketplace/img/avideo_logo.png" class="img img-responsive" />
        </div>
        <div class="alert alert-success">
            <i class="fas fa-cog fa-spin"></i> <?php printf(__("You are running AVideo version %s!"), $config->getVersion()); ?>
        </div>
        <?php
        if (empty($_POST['updateFile'])) {
            $updateFiles = getUpdatesFilesArray();
            if (!empty($updateFiles)) {
        ?>
                <div class="alert alert-warning">
                    <form method="post" class="form-compact well form-horizontal">
                        <fieldset>
                            <legend><?php echo __("Update AVideo System"); ?></legend>
                            <label for="updateFile" class="sr-only"><?php echo __("Select the update"); ?></label>
                            <select class="form-control input-lg selectpicker" data-width="fit" name="updateFile" id="updateFile" required autofocus>
                                <?php
                                $disabled = '';
                                foreach ($updateFiles as $value) {
                                    echo "<option value=\"{$value['filename']}\" {$disabled}>Version {$value['version']}</option>";
                                    $disabled = "disabled";
                                } ?>
                            </select>
                            <?php printf(__("We detected a total of %s pending updates, if you want to do it now click (Update Now) button"), "<strong class='badge'>" . count($updateFiles) . "</strong>"); ?>
                            <hr>
                            <button type="submit" class="btn btn-warning btn-lg center-block " href="?update=1"> <i class="fa-solid fa-arrows-rotate"></i> <?php echo __("Update Now"); ?> </button>
                        </fieldset>
                    </form>
                </div>

                <script>
                    $(document).ready(function() {
                        $('#updateFile').selectpicker();
                    });
                </script>
            <?php
            } elseif ($version = thereIsAnyRemoteUpdate()) {
            ?>
                <div class="alert alert-warning">
                    Our repository is now running at version <?php echo $version->version; ?>.
                    You can follow this <a target="_blank" href="https://github.com/WWBN/AVideo/wiki/How-to-Update-your-AVideo-Platform" class="btn btn-warning btn-xs" rel="noopener noreferrer">Update Tutorial</a>
                    to update your files and get the latest version.
                </div>
            <?php
            } else {
            ?>
                <div class="alert alert-success">
                    <h2><i class="fas fa-check"></i> <?php echo __("Your system is up to date"); ?></h2>
                </div>
            <?php
            }
        } else {
            $obj = new stdClass();
            $templine = '';
            $logfile = Video::getStoragePath() . "avideo.";
            if (file_exists($logfile . "log")) {
                unlink($logfile . "log");
                _error_log("avideo.log deleted by update");
            }
            if (file_exists($logfile . "js.log")) {
                unlink($logfile . "js.log");
                _error_log("avideo.js.log deleted by update");
            }
            $lines = file("{$global['systemRootPath']}updatedb/{$_POST['updateFile']}");
            $obj->error = '';
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '') {
                    continue;
                }
                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    //echo $templine;echo '<br><br>';
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
                if ($global['mysqli']->query($sql) !== true) {
                    $obj->error = "Error deleting configuration: " . $global['mysqli']->error;
                    echo json_encode($obj);
                    exit;
                }

                $sql = "INSERT INTO configurations (id, video_resolution, users_id, version,  created, modified) VALUES (1, '426:240', " . User::getId() . ",'1.0', now(), now())";
                if ($global['mysqli']->query($sql) !== true) {
                    $obj->error = "Error creating configuration: " . $global['mysqli']->error;
                    echo json_encode($obj);
                    exit;
                }
            }

            if ($config->currentVersionEqual('1.0')) {
                $sql = "UPDATE configurations SET  users_id = " . User::getId() . ", version = '1.1', webSiteTitle = '{$global['webSiteTitle']}', language = '{$global['language']}', contactEmail = '{$global['contactEmail']}', modified = now() WHERE id = 1";
                if ($global['mysqli']->query($sql) !== true) {
                    $obj->error = "Error creating configuration: " . $global['mysqli']->error;
                    echo json_encode($obj);
                    exit;
                }
            }

            //$renamed = rename("{$global['systemRootPath']}updateDb.sql", "{$global['systemRootPath']}updateDb.sql.old");
            ?>
            <div class="alert alert-success">
                <?php
                printf(__("Your update from file %s is done, click continue"), $_POST['updateFile']); ?>
                <hr>
                <a class="btn btn-success" href="?done=1"> <i class="fa-solid fa-circle-check"></i> <?php echo __("Continue"); ?> </a>
            </div>
        <?php
        }
        ?>
    </div>
</div>
<?php
$_page->print();
?>
