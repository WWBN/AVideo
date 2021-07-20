<?php
require_once '../../videos/configuration.php';
if (!User::isAdmin()) {
    forbiddenPage();
}

if(!AVideoPlugin::isEnabledByName('User_Location')){
    forbiddenPage('User_Location plugin is disabled');
}

if(!IP2Location::isTableInstalled()){
    forbiddenPage('You MUST install IP2Location tables');
}

$ip = getRealIpAddr();
if (!empty($_REQUEST['ip'])) {
    $ip = $_REQUEST['ip'];
}

$location = IP2Location::getLocation($ip);


?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title>Test IP</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container"> 
            <form action="testIP.php">
                <div class="panel panle-default">
                    <div class="panel-heading">
                        Test IP
                    </div>
                    <div class="panel-body">
                        <label for="ip"><?php echo __("IP Address"); ?>:</label>
                        <input type="text" name="ip" id="ip" class="form-control" value="<?php echo $ip; ?>">
                        <?php
                        if(!empty($location)){
                            echo '<div class="alert alert-success">';
                            foreach ($location as $key => $value) {
                                echo "<strong>{$key}:</strong> {$value}<br>";
                            }
                            echo '</div>';
                        }else{
                            echo '<div class="alert alert-danger">IP Not Found</div>';
                        }
                        ?>
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-success btn-block" id="save"><i class="fas fa-globe-americas"></i> Locate IP</button>
                    </div>
                </div>
            </form>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            $(document).ready(function () {

            });
        </script>
    </body>
</html>
