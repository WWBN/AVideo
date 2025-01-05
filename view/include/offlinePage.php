<?php

if(!function_exists('__')){
    function __($txt){
        return $txt;
    }
}

function isDiskFull() {
    $diskFreeSpace = disk_free_space("/");
    $diskTotalSpace = disk_total_space("/");
    $diskUsagePercentage = (($diskTotalSpace - $diskFreeSpace) / $diskTotalSpace) * 100;

    if ($diskFreeSpace === false || $diskTotalSpace === false) {
        return 'unknown';
    }

    if ($diskUsagePercentage >= 95) {
        return 'full';
    } elseif ($diskUsagePercentage >= 90) {
        return 'almost_full';
    }

    return 'ok';
}

function isMySQLRunning() {
    global $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase;

    try {
        $mysqli = new mysqli($mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase);
        if ($mysqli->connect_error) {
            return false;
        }
        $mysqli->close();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function logAdminMessage($message) {
    error_log($message);
}

if (empty($mysqlHost)) {
    die();
}

$diskStatus = isDiskFull();
$mysqlStatus = isMySQLRunning();

if ($diskStatus === 'full') {
    logAdminMessage("ERROR: The server's disk is full. Immediate action is required.");
} elseif ($diskStatus === 'almost_full') {
    logAdminMessage("WARNING: The server's disk is almost full. Maintenance is recommended soon.");
}

if (!$mysqlStatus) {
    logAdminMessage("ERROR: Unable to connect to MySQL. Check database configuration and server status.");
}

?>
<!doctype html>
<title><?php echo __("Site Maintenance"); ?></title>
<style>
  body { text-align: center; padding: 150px; }
  h1 { font-size: 50px; }
  body { font: 20px Helvetica, sans-serif; color: #333; }
  article { display: block; text-align: left; width: 650px; margin: 0 auto; }
  a { color: #dc8100; text-decoration: none; }
  a:hover { color: #333; text-decoration: none; }
</style>

<article>
    <center>
        <img src="videos/userPhoto/logo.png"/>
    </center>
    <h1><?php echo __("We&rsquo;ll be back soon!"); ?></h1>
    <div>
        <?php if ($diskStatus === 'full'): ?>
            <p><?php echo __("The site is temporarily unavailable because the server's disk is full. We are working on this issue and will be back shortly."); ?></p>
        <?php elseif ($diskStatus === 'almost_full'): ?>
            <p><?php echo __("The site is undergoing maintenance as the server's disk is nearing capacity. We are addressing the issue and will be back shortly."); ?></p>
        <?php elseif (!$mysqlStatus): ?>
            <p><?php echo __("The site is currently offline due to a database issue. We are working to resolve the problem as quickly as possible."); ?></p>
        <?php else: ?>
            <p><?php echo __("Sorry for the inconvenience, but we&rsquo;re performing some maintenance at the moment. Please check back soon."); ?></p>
        <?php endif; ?>

        <p><?php echo __("&mdash; The Team"); ?></p>
    </div>
</article>
