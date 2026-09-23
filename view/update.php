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
require_once $global['systemRootPath'] . 'objects/functionsUpdate.php';
$updateFiles = getUpdatesFilesArray();
$versionOverview = getAVideoUpdateOverview($global['systemRootPath']);
$_page = new Page(array('Update AVideo System'), 'system-update-page');
$_page->setExtraStyles(['view/css/update.css']);
?>
<div class="container-fluid">
    <div class="update-content">
        <?php require $global['systemRootPath'] . 'view/update.version.php'; ?>
        <?php
        if (empty($_POST['updateFile'])) {
            if (!empty($updateFiles)) {
        ?>
                <div class="alert alert-warning">
                    <form method="post" class="form-compact well form-horizontal">
                        <fieldset>
                            <legend><?php echo __('Database updates'); ?></legend>
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
                            <button type="submit" class="btn btn-warning btn-lg center-block"> <i class="fa-solid fa-arrows-rotate"></i> <?php echo __("Update Now"); ?> </button>
                        </fieldset>
                    </form>
                </div>

                <script>
                    $(document).ready(function() {
                        try {
                            $('#updateFile').selectpicker();
                        } catch (error) {
                            console.warn('selectpicker not found, ignoring');
                        }
                    });
                </script>
            <?php
            } elseif ($version = thereIsAnyRemoteUpdate()) {
            ?>
                <div class="alert alert-warning">
                    <?php printf(__('Database schema %s is available. Update the application files first, then return here to apply pending migrations.'), htmlspecialchars($version->version, ENT_QUOTES, 'UTF-8')); ?>
                    <a target="_blank" href="https://github.com/WWBN/AVideo/wiki/How-to-Update-your-AVideo-Platform" class="btn btn-warning btn-xs" rel="noopener noreferrer"><?php echo __('Update guide'); ?></a>
                </div>
            <?php
            }
        } else {
            $allowedUpdateFiles = array_column(getUpdatesFilesArray(), 'filename');
            if (!in_array($_POST['updateFile'], $allowedUpdateFiles, true)) {
                forbiddenPage('Invalid update file', true);
            }
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
                    if (!$global['mysqli']->query($templine)) {
                        $obj->error = ('Error performing query \'<strong>' . $templine . '\': ' . $global['mysqli']->error . '<br /><br />');
                        echo json_encode($obj);
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
