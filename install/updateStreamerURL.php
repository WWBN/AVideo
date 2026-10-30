<?php
/**
 * Changes the address (domain) of this AVideo site everywhere it is saved.
 *
 *   sudo php install/updateStreamerURL.php --new-url=https://new-domain.com/
 *
 * It first shows everything it will change and asks for confirmation. It changes:
 *  1. videos/configuration.php (webSiteRootURL), validated with php -l before it replaces the file;
 *  2. the database: plugin parameters, site configuration, live servers and any other saved
 *     address of the site (see install/updateStreamerURL-functions.php for what counts as an
 *     address; e-mails, folders and other domains are kept). Caches, logs, statistics and
 *     serialized values are not changed;
 *  3. the Encoder installed in <AVideo>/Encoder/: its configuration.php, the site address saved
 *     for each user (streamers.siteURL), Allowed Streamers Sites and the queued jobs of this site.
 *     Other sites served by the same Encoder are not changed;
 *  4. the Apache sites (/etc/apache2/sites-available, /etc/httpd/conf.d) and NGINX
 *     (/usr/local/nginx/conf) files that use the old domain, checked with configtest/nginx -t
 *     (restored when the check fails) and reloaded;
 *  5. a new Let's Encrypt certificate for the new domain (certbot --apache --redirect, which also
 *     installs it in the Apache site), then the certificate paths in NGINX, Apache and the plugin
 *     parameters (YPTSocket...).
 * Then it clears the cache and restarts the YPTSocket server.
 *
 * Point the new domain's DNS to this server before running it (Let's Encrypt checks it).
 * Only the domain, http/https and the port can change: to move the site to another folder,
 * move the files and change the web server first.
 *
 * Options:
 *   --new-url=URL         new address, for example https://new-domain.com/ (asked when missing)
 *   --old-url=URL         old address (default: webSiteRootURL of videos/configuration.php). Use it
 *                         to run again after configuration.php already has the new address, for
 *                         example when the certificate could not be created the first time
 *   --dry-run             only show what would change
 *   --yes                 do not ask for confirmation
 *   --email=EMAIL         e-mail for Let's Encrypt (default: the site contact e-mail)
 *   --skip-database --skip-encoder --skip-apache --skip-nginx --skip-ssl
 *   --encoder-config=FILE Encoder configuration.php (default: <AVideo>/Encoder/videos/configuration.php)
 *   --apache-dir=DIR      folder with the Apache site files (can repeat)
 *   --nginx-dir=DIR       NGINX configuration folder (default: /usr/local/nginx/conf)
 *   --backup-dir=DIR      where the copies of the changed files and values go
 *                         (default: ~/avideo-url-change-backups/<date>, outside the site)
 */
if (PHP_SAPI !== 'cli') {
    die('Command Line only');
}

$options = getopt('', ['new-url:', 'old-url:', 'dry-run', 'yes', 'email:', 'skip-database', 'skip-encoder', 'skip-apache', 'skip-nginx', 'skip-ssl', 'encoder-config:', 'apache-dir:', 'nginx-dir:', 'backup-dir:', 'help']);
if (isset($options['help'])) {
    $help = file_get_contents(__FILE__);
    preg_match('~/\*\*(.*?)\*/~s', $help, $matches);
    echo preg_replace('~^ ?\* ?~m', '', $matches[1]) . PHP_EOL;
    exit(0);
}

require_once dirname(__DIR__) . '/videos/configuration.php';
require_once __DIR__ . '/updateStreamerURL-functions.php';
while (ob_get_level() > 0) {
    ob_end_flush();
}
// Reading every table is slow on purpose; the slow-query log would also print $_SERVER.
$global['noDebugSlowProcess'] = 1;

$dryRun = isset($options['dry-run']);
$isRoot = function_exists('posix_geteuid') && posix_geteuid() === 0;
$isDocker = file_exists('/.dockerenv');
$root = $global['systemRootPath'];

$oldURL = siteURLChangeNormalizeURL($options['old-url'] ?? $global['webSiteRootURL']);
if (empty($oldURL)) {
    siteURLChangeFail('The old address is not valid: ' . ($options['old-url'] ?? $global['webSiteRootURL']));
}
$newInput = $options['new-url'] ?? siteURLChangeAsk("Current address: {$oldURL}" . PHP_EOL . 'Enter the new address (for example https://new-domain.com/): ');
$newURL = siteURLChangeNormalizeURL($newInput);
if (empty($newURL)) {
    siteURLChangeFail('The new address is not valid. Use the full address, for example https://new-domain.com/');
}
if ($oldURL === $newURL) {
    siteURLChangeFail("The new address is the same as the old one ({$oldURL}). If configuration.php already has the new address, run again with --old-url=<old address>.");
}
if (parse_url($oldURL, PHP_URL_PATH) !== parse_url($newURL, PHP_URL_PATH)) {
    siteURLChangeFail('The folder of the address changed (' . parse_url($oldURL, PHP_URL_PATH) . ' to ' . parse_url($newURL, PHP_URL_PATH) . '). This script changes only the domain, http/https and the port: move the files and change the web server first.');
}
$oldHost = parse_url($oldURL, PHP_URL_HOST);
$newHost = parse_url($newURL, PHP_URL_HOST);
$hostChanged = $oldHost !== $newHost;

if ($isDocker) {
    echo 'Running inside Docker: the containers create the web server files and the certificate from SERVER_NAME in .env.' . PHP_EOL;
    echo 'Change SERVER_NAME there and restart the containers. Apache, NGINX and SSL are skipped here' . PHP_EOL;
    echo '(unless --apache-dir/--nginx-dir is given).' . PHP_EOL . PHP_EOL;
    $options['skip-ssl'] = false;
    if (!isset($options['apache-dir'])) {
        $options['skip-apache'] = false;
    }
    if (!isset($options['nginx-dir'])) {
        $options['skip-nginx'] = false;
    }
}

echo "Old address: {$oldURL}" . PHP_EOL;
echo "New address: {$newURL}" . PHP_EOL . PHP_EOL;
$warnings = [];
$nextSteps = [];

// --- SSL plan (decides whether certificate paths will change) -------------------------------
$certbot = siteURLChangeFindExecutable(['/usr/bin/certbot', '/usr/local/bin/certbot', '/snap/bin/certbot']);
$newCertDir = "/etc/letsencrypt/live/{$newHost}/";
$sslPlanned = false;
$sslReason = '';
if ($isDocker) {
    $sslReason = 'skipped: the Docker containers create it from SERVER_NAME';
} elseif (isset($options['skip-ssl'])) {
    $sslReason = 'skipped (--skip-ssl)';
} elseif (parse_url($newURL, PHP_URL_SCHEME) !== 'https') {
    $sslReason = 'not needed (the new address uses http)';
} elseif (!siteURLChangeCanHaveCertificate($newHost)) {
    $sslReason = "not possible for {$newHost} (Let's Encrypt needs a public domain name)";
} elseif (!$isRoot) {
    $sslReason = 'skipped: run as root (sudo) to create the certificate';
} elseif (empty($certbot)) {
    $sslReason = 'skipped: certbot is not installed (sudo apt install certbot python3-certbot-apache)';
} else {
    $sslPlanned = true;
    if (!gethostbynamel($newHost)) {
        $warnings[] = "{$newHost} has no DNS record yet: Let's Encrypt will refuse the certificate. Point the domain to this server first.";
    }
}
$plannedCertName = ($sslPlanned || is_file($newCertDir . 'fullchain.pem')) ? $newHost : null;
$map = siteURLChangeCreateMap($oldURL, $newURL, $plannedCertName);

// --- 1. configuration.php ---------------------------------------------------------------------
$configFile = $root . 'videos/configuration.php';
$configPlan = siteURLChangePlanConfigFile($configFile, $map, $newURL);
echo '1. ' . $configFile . PHP_EOL;
siteURLChangePrintConfigPlan($configPlan);

// --- 2. Streamer database ---------------------------------------------------------------------
$encoderConfigFile = $options['encoder-config'] ?? $root . 'Encoder/videos/configuration.php';
$encoderPlan = null;
if (!isset($options['skip-encoder']) && is_file($encoderConfigFile)) {
    $encoderPlan = siteURLChangePlanEncoder($encoderConfigFile, $map);
}
$streamerSkipTables = [];
if (!empty($encoderPlan['sharedDatabase'])) {
    // Encoder tables in the same database belong to every site the Encoder serves.
    foreach (['streamers', 'encoder_queue', 'upload_queue', 'configurations_encoder', 'formats', 'encoder_queue_monitor', 'encoder_monitor_state'] as $table) {
        $streamerSkipTables[] = $encoderPlan['prefix'] . $table;
    }
}
$databasePlan = null;
echo PHP_EOL . '2. Database' . PHP_EOL;
if (isset($options['skip-database'])) {
    echo '   skipped (--skip-database)' . PHP_EOL;
} else {
    $databasePlan = siteURLChangeScanDatabase($map, $streamerSkipTables);
    siteURLChangePrintDatabasePlan($databasePlan);
}

// --- 3. Encoder ------------------------------------------------------------------------------
echo PHP_EOL . '3. Encoder' . PHP_EOL;
if (isset($options['skip-encoder'])) {
    echo '   skipped (--skip-encoder)' . PHP_EOL;
} elseif (empty($encoderPlan)) {
    echo "   not found in {$encoderConfigFile}" . PHP_EOL;
    $nextSteps[] = 'If you use an Encoder on another server, add the new address to its Allowed Streamers Sites (Encoder > Configurations).';
} else {
    siteURLChangePrintEncoderPlan($encoderPlan);
}

// --- 4. Web servers ---------------------------------------------------------------------------
$apacheDirs = isset($options['apache-dir']) ? (array) $options['apache-dir'] : ['/etc/apache2/sites-available', '/etc/apache2/sites-enabled', '/etc/httpd/conf.d', '/etc/httpd/conf'];
$nginxDirs = isset($options['nginx-dir']) ? (array) $options['nginx-dir'] : ['/usr/local/nginx/conf'];
$apachePlan = isset($options['skip-apache']) ? null : siteURLChangePlanServerFiles(siteURLChangeListConfigFiles($apacheDirs, false), $map);
$nginxPlan = isset($options['skip-nginx']) ? null : siteURLChangePlanServerFiles(siteURLChangeListConfigFiles($nginxDirs, true), $map);
$apachectl = siteURLChangeFindExecutable(['/usr/sbin/apache2ctl', '/usr/sbin/apachectl', '/usr/bin/apachectl', '/usr/local/apache2/bin/apachectl']);
$nginxBin = siteURLChangeFindExecutable(['/usr/local/nginx/sbin/nginx']);

echo PHP_EOL . '4. Apache' . PHP_EOL;
siteURLChangePrintServerPlan($apachePlan, isset($options['skip-apache']), $isRoot);
echo PHP_EOL . '5. NGINX' . PHP_EOL;
siteURLChangePrintServerPlan($nginxPlan, isset($options['skip-nginx']), $isRoot);

$certDomains = [$newHost];
if ($sslPlanned && strpos($newHost, 'www.') !== 0 && siteURLChangeServerFilesMention(array_merge($apachePlan['files'] ?? [], $nginxPlan['files'] ?? []), 'www.' . $oldHost) && gethostbynamel('www.' . $newHost)) {
    $certDomains[] = 'www.' . $newHost;
}
echo PHP_EOL . "6. Let's Encrypt certificate" . PHP_EOL;
if ($sslPlanned) {
    echo '   request a certificate for ' . implode(', ', $certDomains) . " ({$newCertDir})" . PHP_EOL;
} else {
    echo "   {$sslReason}" . PHP_EOL;
    if (empty($plannedCertName)) {
        echo '   certificate paths (/etc/letsencrypt/live/' . $oldHost . '/) are kept' . PHP_EOL;
    }
}

// Other places of this server that can use the domain; the script only lists them.
$otherFiles = array_merge(
    ['/etc/hosts', '/etc/crontab'],
    glob('/etc/cron.d/*') ?: [],
    glob('/var/spool/cron/crontabs/*') ?: [],
    glob('/etc/systemd/system/*.service') ?: [],
    in_array('/etc/nginx', $nginxDirs, true) ? [] : siteURLChangeListConfigFiles(['/etc/nginx'], true)
);
$otherFiles = array_diff(array_filter(array_map('realpath', $otherFiles)), array_keys($apachePlan['files'] ?? []), array_keys($nginxPlan['files'] ?? []));
$mentions = $hostChanged ? siteURLChangeFindMentions($otherFiles, $map) : [];
if (!empty($mentions)) {
    echo PHP_EOL . '7. Other files of this server that use the old domain (NOT changed, check them by hand)' . PHP_EOL;
    foreach ($mentions as $where => $line) {
        echo "   {$where}: {$line}" . PHP_EOL;
    }
    $nextSteps[] = 'Check the files listed in step 7 (hosts, crontabs, services): this script does not change them.';
}

if (!empty($nginxPlan['files']) && $isRoot) {
    $warnings[] = 'NGINX will be reloaded: lives in progress may disconnect and have to start again.';
}
foreach ($warnings as $warning) {
    echo PHP_EOL . 'WARNING: ' . $warning . PHP_EOL;
}

if ($dryRun) {
    echo PHP_EOL . 'Dry run: nothing was changed.' . PHP_EOL;
    exit(0);
}
if (!isset($options['yes']) && strtolower(siteURLChangeAsk(PHP_EOL . 'Type "yes" to change the address: ')) !== 'yes') {
    echo 'Nothing was changed.' . PHP_EOL;
    exit(1);
}

// --- Apply ------------------------------------------------------------------------------------
$backupDir = siteURLChangeCreateBackupDir($options['backup-dir'] ?? null);
echo PHP_EOL . "Copies of everything changed: {$backupDir}" . PHP_EOL;
_error_log("updateStreamerURL: changing the site address from {$oldURL} to {$newURL}");
$errors = [];

// Web servers first: Let's Encrypt checks the new domain through Apache.
$serverGroups = [];
if (!empty($apachePlan['files']) && $isRoot) {
    $serverGroups['Apache'] = ['plan' => $apachePlan, 'test' => $apachectl ? [$apachectl, 'configtest'] : null, 'reload' => $apachectl ? [$apachectl, 'graceful'] : null];
}
if (!empty($nginxPlan['files']) && $isRoot) {
    $nginxReload = ($nginxBin && is_file('/usr/local/nginx/logs/nginx.pid')) ? [$nginxBin, '-s', 'reload'] : null;
    $serverGroups['NGINX'] = ['plan' => $nginxPlan, 'test' => $nginxBin ? [$nginxBin, '-t'] : null, 'reload' => $nginxReload];
}
$mapWithoutCertificate = siteURLChangeCreateMap($oldURL, $newURL, null);
foreach ($serverGroups as $name => $group) {
    if (!siteURLChangeApplyServerFiles($name, $group, $mapWithoutCertificate, $backupDir)) {
        $errors[] = "{$name}: the new configuration did not pass the check and the old files were restored.";
        $sslPlanned = $sslPlanned && $name !== 'Apache';
    }
}

$certName = is_file($newCertDir . 'fullchain.pem') ? $newHost : null;
if ($sslPlanned) {
    echo PHP_EOL . "Requesting the Let's Encrypt certificate..." . PHP_EOL;
    // With Apache, certbot also installs the certificate in the site of the new domain (and creates
    // the HTTPS site and the http->https redirect when the old address used http), as the
    // installation guide does. Without Apache it only creates the files.
    $command = $apachectl
        ? [$certbot, '--apache', '--redirect']
        : [$certbot, 'certonly', '--webroot', '-w', rtrim($root, '/')];
    array_push($command, '--non-interactive', '--agree-tos', '--no-eff-email', '--keep-until-expiring', '--cert-name', $newHost);
    foreach ($certDomains as $domain) {
        array_push($command, '-d', $domain);
    }
    $email = $options['email'] ?? (new AVideoConf())->getContactEmail();
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        array_push($command, '-m', $email);
    } else {
        $command[] = '--register-unsafely-without-email';
    }
    if (siteURLChangeRun($command, true) === 0 && is_file($newCertDir . 'fullchain.pem')) {
        $certName = $newHost;
        echo "Certificate ready: {$newCertDir}" . PHP_EOL;
    } else {
        $certName = null;
        $errors[] = "The Let's Encrypt certificate was not created (see the certbot message above). The certificate paths still point to the old domain."
            . " Fix the DNS/firewall (ports 80 and 443) and run: sudo php {$root}install/updateStreamerURL.php --old-url={$oldURL} --new-url={$newURL}";
    }
}
$finalMap = siteURLChangeCreateMap($oldURL, $newURL, $certName);
if ($certName) {
    foreach ($serverGroups as $name => $group) {
        if (!siteURLChangeApplyServerFiles($name, $group, $finalMap, $backupDir)) {
            $errors[] = "{$name}: the certificate paths did not pass the check and the previous files were restored.";
        }
    }
}

// configuration.php is validated before the database changes and replaced right after them.
$configReady = null;
if (!empty($configPlan['error'])) {
    $errors[] = "configuration.php was not changed ({$configPlan['error']}): set webSiteRootURL to {$newURL} by hand.";
}
if (!empty($configPlan['content'])) {
    try {
        $configReady = siteURLChangePrepareFile($configFile, $configPlan['content'], true);
    } catch (\Throwable $th) {
        $errors[] = 'configuration.php was not changed: ' . $th->getMessage() . '. The database and the Encoder were not changed either.';
    }
}
// The site (configuration.php + database) changes together; the Encoder follows only when it did.
$streamerFailed = !empty($configPlan['content']) && !$configReady;
$databaseChanged = false;
if (!empty($databasePlan) && !$streamerFailed) {
    $databasePlan = siteURLChangeScanDatabase($finalMap, $streamerSkipTables);
    try {
        siteURLChangeApplyDatabase($databasePlan, $backupDir);
        $databaseChanged = !empty($databasePlan['changes']);
        $after = siteURLChangeScanDatabase($finalMap, $streamerSkipTables);
        $pending = count($after['changes']);
        echo PHP_EOL . 'Database: ' . (count($databasePlan['changes']) - $pending) . ' values changed.' . PHP_EOL;
        if ($pending > 0) {
            $errors[] = "{$pending} database value(s) still have the old address: " . implode(', ', array_keys($after['summary'])) . ". Check them in the log above or run the script again with --old-url={$oldURL}";
        }
    } catch (\Throwable $th) {
        _error_log('updateStreamerURL: database ' . $th->getMessage(), AVideoLog::$ERROR);
        $errors[] = 'The database was not changed (everything was rolled back): ' . $th->getMessage() . '. configuration.php and the Encoder were not changed either.';
        siteURLChangeDiscardFile($configReady);
        $configReady = null;
        $streamerFailed = true;
    }
}
if ($configReady) {
    try {
        siteURLChangeCommitFile($configReady, $backupDir);
        echo "configuration.php: webSiteRootURL = {$newURL}" . PHP_EOL;
    } catch (\Throwable $th) {
        $errors[] = 'configuration.php was not changed: ' . $th->getMessage() . ". The database already has the new address: set webSiteRootURL to {$newURL} by hand.";
    }
}

if (!empty($encoderPlan) && !$streamerFailed) {
    $encoderPlan = siteURLChangePlanEncoder($encoderConfigFile, $finalMap);
    try {
        siteURLChangeApplyEncoder($encoderPlan, $backupDir);
    } catch (\Throwable $th) {
        _error_log('updateStreamerURL: Encoder ' . $th->getMessage(), AVideoLog::$ERROR);
        $errors[] = 'Encoder: ' . $th->getMessage();
    }
}

$siteChanged = $databaseChanged || $configReady;
echo PHP_EOL . 'Clearing the cache...' . PHP_EOL;
ObjectYPT::deleteALLCache();
if ($siteChanged) {
    $deleted = 0;
    foreach (siteURLChangeListGeneratedCacheFiles(getVideosDir()) as $file) {
        $deleted += @unlink($file) ? 1 : 0;
    }
    echo "Generated video source/EPG cache files deleted: {$deleted}" . PHP_EOL;
}

// PHP keeps configuration.php in OPcache and objects in APCu per web server process: reload them.
if ($siteChanged && $isRoot) {
    if ($apachectl && siteURLChangeRun([$apachectl, 'graceful'], false, $output) === 0) {
        echo 'Apache reloaded.' . PHP_EOL;
    }
    $systemctl = siteURLChangeFindExecutable(['/usr/bin/systemctl', '/bin/systemctl']);
    foreach (glob('/lib/systemd/system/php*-fpm.service') ?: [] as $unit) {
        if ($systemctl && siteURLChangeRun([$systemctl, 'reload', basename($unit)], false, $output) === 0) {
            echo basename($unit, '.service') . ' reloaded.' . PHP_EOL;
        }
    }
}

if (AVideoPlugin::isEnabledByName('YPTSocket') && $siteChanged) {
    echo 'Restarting the YPTSocket server...' . PHP_EOL;
    siteURLChangeRun([PHP_BINARY, $root . 'plugin/YPTSocket/serverRestart.php', 'force'], true);
}

// WebRTC2RTMP reads the domain and its certificate only when it starts, and its own certificate
// check (WebRTC::restartIfCertificateIsOutdated()) looks at the domain it already uses.
if ($siteChanged && $hostChanged && AVideoPlugin::loadPluginIfEnabled('WebRTC')) {
    $webRTC = AVideoPlugin::getDataObject('WebRTC');
    $pid = getPIDUsingPort($webRTC->port);
    if (!empty($pid) && WebRTC::hasChildProcesses($pid) !== false) {
        $nextSteps[] = "A WebRTC live is running, so the WebRTC server was not restarted. When it ends, run: sudo php {$root}plugin/WebRTC/tools/forceAll.php";
    } elseif (!empty($pid)) {
        echo 'Restarting the WebRTC server...' . PHP_EOL;
        siteURLChangeRun([PHP_BINARY, $root . 'plugin/WebRTC/tools/stop.php'], false, $output);
        if (empty($webRTC->autoStartServerIfIsInactive)) {
            siteURLChangeRun([PHP_BINARY, $root . 'plugin/WebRTC/tools/start.php'], false, $output);
        } else {
            echo 'The Scheduler cron starts it again within a minute.' . PHP_EOL;
        }
    }
}

// --- Summary ----------------------------------------------------------------------------------
if ($hostChanged) {
    $nextSteps[] = "Make the old domain redirect to {$newURL} (at your DNS/CDN provider or in a separate web server site), so old links keep working.";
    if (is_dir("/etc/letsencrypt/live/{$oldHost}/")) {
        $nextSteps[] = "When the old domain no longer points to this server, delete its certificate so certbot stops renewing it: sudo certbot delete --cert-name {$oldHost}";
    }
}
$nextSteps[] = 'Update the addresses registered outside AVideo: social login apps (Google, Facebook...), payment webhooks (PayPal, Stripe...), the mobile app, the origin of your CDN, and embeds on other sites.';
$nextSteps[] = 'Servers of their own keep the old address in their files: a live server on another machine ($streamerURL in plugin/Live/standAloneFiles/*.php there), an Encoder on another server (Allowed Streamers Sites, and sign in again from the new site), YPTStorage servers.';
$nextSteps[] = 'Everyone has to sign in again on the new address (browsers keep the session per domain).';
if ($hostChanged && is_dir("{$root}Encoder/videos/watermarked/{$oldHost}/")) {
    $nextSteps[] = "The Encoder watermark cache of the old domain is no longer used and can be deleted: {$root}Encoder/videos/watermarked/{$oldHost}/";
}
echo PHP_EOL . (empty($errors) ? 'Done.' : 'Finished with errors:') . PHP_EOL;
foreach ($errors as $error) {
    echo ' - ' . $error . PHP_EOL;
}
echo PHP_EOL . 'Next steps:' . PHP_EOL;
foreach ($nextSteps as $step) {
    echo ' - ' . $step . PHP_EOL;
}
echo PHP_EOL . "To undo, copy the files back from {$backupDir}/files/ (the database values are in {$backupDir}/*.json)." . PHP_EOL;
exit(empty($errors) ? 0 : 1);

// =============================================================================================

function siteURLChangeFail($message)
{
    echo $message . PHP_EOL;
    exit(1);
}

function siteURLChangeAsk($question)
{
    echo $question;
    $line = fgets(STDIN);
    return trim((string) $line);
}

function siteURLChangeCreateBackupDir($dir)
{
    if (empty($dir)) {
        $home = getenv('HOME');
        if (empty($home) && function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
            $home = posix_getpwuid(posix_geteuid())['dir'] ?? '';
        }
        $base = (!empty($home) && is_writable($home)) ? $home : sys_get_temp_dir();
        $dir = rtrim($base, '/') . '/avideo-url-change-backups/' . date('Ymd-His');
    }
    $dir = rtrim($dir, '/');
    if (!is_dir($dir) && !mkdir($dir, 0700, true)) {
        siteURLChangeFail("Could not create the backup folder {$dir}. Use --backup-dir=<folder>.");
    }
    @chmod($dir, 0700);
    return $dir;
}

// --- configuration.php ------------------------------------------------------------------------

/**
 * Plans the change of a configuration.php: every plain text setting with the site address
 * (siteURLChangeReplacePHPStringLiterals()); with $siteURL, webSiteRootURL must end with it.
 */
function siteURLChangePlanConfigFile($file, $map, $siteURL = null)
{
    $plan = ['file' => $file, 'old' => null, 'content' => null, 'error' => null, 'expressions' => 0, 'changes' => [], 'remaining' => [], 'mysqlHost' => false];
    $code = @file_get_contents($file);
    if ($code === false) {
        $plan['error'] = 'cannot read the file';
        return $plan;
    }
    $assignments = siteURLChangeReadPHPAssignments($code);
    $plan['old'] = $assignments['global.webSiteRootURL']['value'] ?? null;
    $plan['expressions'] = siteURLChangeCountExpressionAssignments($code, 'global.webSiteRootURL');
    $plan['mysqlHost'] = !empty($assignments['mysqlHost']['literal']) && strtolower((string) $assignments['mysqlHost']['value']) === $map['oldHost'];
    $result = siteURLChangeReplacePHPStringLiterals($code, $map);
    $content = $result['content'];
    $plan['changes'] = $result['changes'];
    $plan['remaining'] = $result['remaining'];
    if ($siteURL !== null) {
        try {
            $forced = siteURLChangeSetPHPAssignments($content, ['global.webSiteRootURL' => $siteURL]);
        } catch (\Throwable $th) {
            $plan['error'] = $th->getMessage();
            return $plan;
        }
        if ($forced !== $content) {
            $plan['changes'][] = ['name' => 'global.webSiteRootURL', 'old' => $plan['old'], 'new' => $siteURL];
            $content = $forced;
        }
    }
    if ($content !== $code) {
        $plan['content'] = $content;
    }
    return $plan;
}

function siteURLChangePrintConfigPlan($plan, $indent = '   ')
{
    if (!empty($plan['error'])) {
        echo "{$indent}NOT POSSIBLE: {$plan['error']}" . PHP_EOL;
    } elseif (empty($plan['content'])) {
        echo "{$indent}no setting has the old address" . PHP_EOL;
    }
    foreach ($plan['changes'] as $change) {
        $name = strpos($change['name'], 'global.') === 0 ? substr($change['name'], 7) : '$' . $change['name'];
        echo "{$indent}{$name}: {$change['old']} -> {$change['new']}" . PHP_EOL;
    }
    if (!empty($plan['expressions'])) {
        echo "{$indent}kept: {$plan['expressions']} webSiteRootURL line(s) built at runtime (for example from the server name)" . PHP_EOL;
    }
    if (!empty($plan['remaining'])) {
        echo "{$indent}CHECK BY HAND: line(s) " . implode(', ', $plan['remaining']) . ' still have the old address (not a plain text setting)' . PHP_EOL;
    }
    if (!empty($plan['mysqlHost'])) {
        echo "{$indent}kept: mysqlHost uses the old domain; change it by hand only if the database server also moves" . PHP_EOL;
    }
}

// --- Streamer database ------------------------------------------------------------------------

function siteURLChangeQuoteName($name)
{
    return '`' . str_replace('`', '``', $name) . '`';
}

/**
 * Finds every saved value of the Streamer database that contains the old address.
 */
function siteURLChangeScanDatabase($map, array $skipTables)
{
    $plan = ['changes' => [], 'summary' => [], 'remaining' => [], 'serialized' => [], 'skipped' => []];
    $sql = "SELECT c.TABLE_NAME AS tableName, c.COLUMN_NAME AS columnName FROM information_schema.COLUMNS c "
        . "JOIN information_schema.TABLES t ON t.TABLE_SCHEMA = c.TABLE_SCHEMA AND t.TABLE_NAME = c.TABLE_NAME "
        . "WHERE c.TABLE_SCHEMA = DATABASE() AND t.TABLE_TYPE = 'BASE TABLE' "
        . "AND c.DATA_TYPE IN ('char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'json') "
        . "ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION";
    $res = sqlDAL::readSql($sql, '', [], true);
    $columns = $res ? sqlDAL::fetchAllAssoc($res) : [];
    sqlDAL::close($res);
    if ($res === false) {
        throw new RuntimeException('could not list the database columns');
    }
    $tables = [];
    foreach ($columns as $column) {
        $tables[$column['tableName']][] = $column['columnName'];
    }
    $res = sqlDAL::readSql("SELECT TABLE_NAME AS tableName, COLUMN_NAME AS columnName FROM information_schema.KEY_COLUMN_USAGE "
        . "WHERE TABLE_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'PRIMARY' ORDER BY TABLE_NAME, ORDINAL_POSITION", '', [], true);
    $primaryKeys = [];
    foreach (($res ? sqlDAL::fetchAllAssoc($res) : []) as $row) {
        $primaryKeys[$row['tableName']][] = $row['columnName'];
    }
    sqlDAL::close($res);

    $like = '%' . addcslashes($map['oldHost'], '%_\\') . '%';
    foreach ($tables as $table => $textColumns) {
        if (siteURLChangeIsSkippedTable($table) || in_array($table, $skipTables, true)) {
            $plan['skipped'][] = $table;
            continue;
        }
        $keys = $primaryKeys[$table] ?? [];
        $select = array_unique(array_merge($keys, $textColumns));
        $where = [];
        foreach ($textColumns as $column) {
            $where[] = siteURLChangeQuoteName($column) . ' LIKE ?';
        }
        $sql = 'SELECT ' . implode(', ', array_map('siteURLChangeQuoteName', $select)) . ' FROM ' . siteURLChangeQuoteName($table)
            . ' WHERE ' . implode(' OR ', $where);
        $res = sqlDAL::readSql($sql, str_repeat('s', count($textColumns)), array_fill(0, count($textColumns), $like), true);
        if ($res === false) {
            throw new RuntimeException("could not read the table {$table}");
        }
        $rows = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        foreach ($rows as $row) {
            $identity = [];
            foreach ($keys as $key) {
                if ($row[$key] === null) {
                    $identity = [];
                    break;
                }
                $identity[$key] = $row[$key];
            }
            foreach ($textColumns as $column) {
                $value = $row[$column];
                if (!is_string($value) || stripos($value, $map['oldHost']) === false) {
                    continue;
                }
                $name = "{$table}.{$column}";
                if (siteURLChangeIsSerialized($value)) {
                    $plan['serialized'][$name] = ($plan['serialized'][$name] ?? 0) + 1;
                    continue;
                }
                $new = siteURLChangeReplaceValue($value, $map);
                if ($new === $value) {
                    $plan['remaining'][$name] = ($plan['remaining'][$name] ?? 0) + 1;
                    continue;
                }
                if (!siteURLChangeKeepsJSONValid($value, $new)) {
                    $plan['serialized'][$name] = ($plan['serialized'][$name] ?? 0) + 1;
                    continue;
                }
                $plan['changes'][] = ['table' => $table, 'column' => $column, 'where' => $identity, 'old' => $value, 'new' => $new];
                $plan['summary'][$name] = ($plan['summary'][$name] ?? 0) + 1;
            }
        }
    }
    return $plan;
}

function siteURLChangePrintDatabasePlan($plan)
{
    if (empty($plan['summary'])) {
        echo '   no saved value has the old address' . PHP_EOL;
    }
    foreach ($plan['summary'] as $name => $count) {
        echo "   {$name}: {$count} " . ($count === 1 ? 'value' : 'values') . PHP_EOL;
    }
    $plugins = [];
    foreach ($plan['changes'] as $change) {
        if ($change['table'] === 'plugins' && !empty($change['where'])) {
            $plugins[] = $change['where']['id'];
        }
    }
    if (!empty($plugins)) {
        $names = [];
        foreach (array_unique($plugins) as $id) {
            $res = sqlDAL::readSql('SELECT name FROM plugins WHERE id = ?', 'i', [$id]);
            $row = $res ? sqlDAL::fetchAssoc($res) : null;
            sqlDAL::close($res);
            $names[] = $row['name'] ?? "#{$id}";
        }
        sort($names);
        echo '   plugins with parameters to change: ' . implode(', ', $names) . PHP_EOL;
    }
    if (!empty($plan['remaining'])) {
        $list = [];
        foreach ($plan['remaining'] as $name => $count) {
            $list[] = "{$name} ({$count})";
        }
        echo '   kept (the old domain appears as text, an e-mail, a folder, a subdomain or a certificate path): ' . implode(', ', $list) . PHP_EOL;
    }
    if (!empty($plan['serialized'])) {
        echo '   NOT CHANGED, serialized values or values that would stop being valid JSON (check them by hand): ' . implode(', ', array_keys($plan['serialized'])) . PHP_EOL;
    }
}

function siteURLChangeApplyDatabase($plan, $backupDir)
{
    if (empty($plan['changes'])) {
        return;
    }
    $backup = [];
    foreach ($plan['changes'] as $change) {
        $backup[] = ['table' => $change['table'], 'column' => $change['column'], 'where' => $change['where'], 'old' => $change['old']];
    }
    $file = $backupDir . '/streamer-database.json';
    if (file_put_contents($file, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) === false) {
        throw new RuntimeException('could not save the copy of the old values');
    }
    @chmod($file, 0600);

    if (!mysqlBeginTransaction()) {
        throw new RuntimeException('could not start a transaction');
    }
    try {
        foreach ($plan['changes'] as $change) {
            $column = siteURLChangeQuoteName($change['column']);
            // The old value is part of the condition: a value changed after the scan is left alone.
            // Compared as utf8mb4 characters: comparing bytes fails on latin1 columns with accents.
            $conditions = ["CONVERT({$column} USING utf8mb4) COLLATE utf8mb4_bin = CONVERT(? USING utf8mb4) COLLATE utf8mb4_bin"];
            $values = [$change['new'], $change['old']];
            foreach ($change['where'] as $key => $value) {
                $conditions[] = siteURLChangeQuoteName($key) . ' = ?';
                $values[] = $value;
            }
            $sql = 'UPDATE ' . siteURLChangeQuoteName($change['table']) . " SET {$column} = ? WHERE " . implode(' AND ', $conditions);
            if (sqlDAL::writeSql($sql, str_repeat('s', count($values)), $values) === false) {
                throw new RuntimeException("could not update {$change['table']}.{$change['column']}");
            }
        }
        if (!mysqlCommit()) {
            throw new RuntimeException('could not commit the transaction');
        }
    } catch (\Throwable $th) {
        mysqlRollback();
        throw $th;
    }
}

// --- Encoder ----------------------------------------------------------------------------------

function siteURLChangePlanEncoder($configFile, $map)
{
    global $mysqlHost, $mysqlDatabase;
    $plan = ['file' => $configFile, 'error' => null, 'config' => null, 'oldURL' => null, 'newURL' => null, 'siteURL' => $map['newURL'], 'streamers' => [], 'allowed' => null, 'queue' => [], 'prefix' => '', 'sharedDatabase' => false];
    $code = @file_get_contents($configFile);
    if ($code === false) {
        $plan['error'] = 'cannot read the Encoder configuration.php';
        return $plan;
    }
    $assignments = siteURLChangeReadPHPAssignments($code);
    $plan['oldURL'] = $assignments['global.webSiteRootURL']['value'] ?? null;
    // The Encoder keeps its own address unless it is on the old domain (for example https://old.com/Encoder/).
    $plan['configPlan'] = siteURLChangePlanConfigFile($configFile, $map);
    $plan['config'] = $plan['configPlan']['content'];
    $plan['newURL'] = siteURLChangeReadPHPAssignments((string) $plan['config'])['global.webSiteRootURL']['value'] ?? $plan['oldURL'];
    $credentials = [];
    foreach (['mysqlHost', 'mysqlUser', 'mysqlPass', 'mysqlDatabase'] as $name) {
        if (empty($assignments[$name]['literal'])) {
            $plan['error'] = "the database settings of the Encoder configuration.php ({$name}) are not plain values; change the Encoder by hand";
            return $plan;
        }
        $credentials[$name] = (string) $assignments[$name]['value'];
    }
    $port = !empty($assignments['mysqlPort']['literal']) ? (int) $assignments['mysqlPort']['value'] : 3306;
    $prefix = !empty($assignments['global.tablesPrefix']['literal']) ? (string) $assignments['global.tablesPrefix']['value'] : '';
    if (!preg_match('/^[A-Za-z0-9_]*$/', $prefix)) {
        $plan['error'] = 'invalid tablesPrefix in the Encoder configuration.php';
        return $plan;
    }
    $plan['prefix'] = $prefix;
    $plan['sharedDatabase'] = $credentials['mysqlDatabase'] === $mysqlDatabase && strtolower($credentials['mysqlHost']) === strtolower($mysqlHost);
    try {
        $mysqli = siteURLChangeEncoderConnection($credentials, $port);
        $result = $mysqli->query('SELECT id, siteURL FROM ' . siteURLChangeQuoteName($prefix . 'streamers'));
        while ($row = $result->fetch_assoc()) {
            if (siteURLChangeIsSameSite($row['siteURL'], $map['oldURL'])) {
                $plan['streamers'][$row['id']] = $row['siteURL'];
            }
        }
        $result->free();
        $result = $mysqli->query('SELECT id, allowedStreamersURL FROM ' . siteURLChangeQuoteName($prefix . 'configurations_encoder'));
        while ($row = $result->fetch_assoc()) {
            $new = siteURLChangeReplaceAllowedStreamers($row['allowedStreamersURL'], $map['oldURL'], $map['newURL']);
            if ($new !== (string) $row['allowedStreamersURL']) {
                $plan['allowed'][$row['id']] = ['old' => $row['allowedStreamersURL'], 'new' => $new];
            }
        }
        $result->free();
        $like = '%' . addcslashes($map['oldHost'], '%_\\') . '%';
        $stmt = $mysqli->prepare('SELECT id, fileURI, videoDownloadedLink, return_vars FROM ' . siteURLChangeQuoteName($prefix . 'encoder_queue')
            . ' WHERE streamers_id = ? AND (fileURI LIKE ? OR videoDownloadedLink LIKE ? OR return_vars LIKE ?)');
        foreach (array_keys($plan['streamers']) as $streamersId) {
            $stmt->bind_param('isss', $streamersId, $like, $like, $like);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                foreach (['fileURI', 'videoDownloadedLink', 'return_vars'] as $column) {
                    $new = siteURLChangeReplaceValue($row[$column], $map);
                    if ($new !== $row[$column]) {
                        $plan['queue'][] = ['id' => (int) $row['id'], 'column' => $column, 'old' => $row[$column], 'new' => $new];
                    }
                }
            }
            $result->free();
        }
        $stmt->close();
        $mysqli->close();
    } catch (\Throwable $th) {
        $plan['error'] = 'could not read the Encoder database: ' . $th->getMessage();
    }
    $plan['credentials'] = $credentials;
    $plan['port'] = $port;
    return $plan;
}

/**
 * The Encoder has its own database; sqlDAL only works with the Streamer connection.
 */
function siteURLChangeEncoderConnection($credentials, $port)
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli($credentials['mysqlHost'], $credentials['mysqlUser'], $credentials['mysqlPass'], $credentials['mysqlDatabase'], $port);
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}

function siteURLChangePrintEncoderPlan($plan)
{
    echo "   {$plan['file']}" . PHP_EOL;
    if (!empty($plan['error'])) {
        echo "   NOT POSSIBLE: {$plan['error']}" . PHP_EOL;
    }
    if (!empty($plan['configPlan'])) {
        siteURLChangePrintConfigPlan($plan['configPlan']);
    }
    echo '   site address of ' . count($plan['streamers']) . ' Encoder user(s) (streamers.siteURL)' . PHP_EOL;
    if (!empty($plan['allowed'])) {
        echo '   Allowed Streamers Sites: the old address becomes the new one' . PHP_EOL;
    }
    if (!empty($plan['queue'])) {
        echo '   ' . count($plan['queue']) . ' value(s) of queued jobs of this site' . PHP_EOL;
    }
    echo '   other sites served by this Encoder are not changed' . PHP_EOL;
}

function siteURLChangeApplyEncoder($plan, $backupDir)
{
    if (!empty($plan['error']) && empty($plan['credentials'])) {
        throw new RuntimeException($plan['error']);
    }
    $prepared = null;
    if (!empty($plan['config'])) {
        $prepared = siteURLChangePrepareFile($plan['file'], $plan['config'], true);
    }
    if (!empty($plan['streamers']) || !empty($plan['allowed']) || !empty($plan['queue'])) {
        $backup = ['streamers' => $plan['streamers'], 'configurations_encoder' => $plan['allowed'], 'encoder_queue' => array_map(function ($change) {
            return ['id' => $change['id'], 'column' => $change['column'], 'old' => $change['old']];
        }, $plan['queue'])];
        $file = $backupDir . '/encoder-database.json';
        file_put_contents($file, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        @chmod($file, 0600);
        $mysqli = siteURLChangeEncoderConnection($plan['credentials'], $plan['port']);
        $prefix = $plan['prefix'];
        $mysqli->begin_transaction();
        try {
            $stmt = $mysqli->prepare('UPDATE ' . siteURLChangeQuoteName($prefix . 'streamers') . ' SET siteURL = ?, modified = NOW() WHERE id = ? AND siteURL = ?');
            $siteURL = $plan['siteURL'];
            foreach ($plan['streamers'] as $id => $old) {
                $stmt->bind_param('sis', $siteURL, $id, $old);
                $stmt->execute();
            }
            $stmt->close();
            if (!empty($plan['allowed'])) {
                $stmt = $mysqli->prepare('UPDATE ' . siteURLChangeQuoteName($prefix . 'configurations_encoder') . ' SET allowedStreamersURL = ?, modified = NOW() WHERE id = ?');
                foreach ($plan['allowed'] as $id => $change) {
                    $stmt->bind_param('si', $change['new'], $id);
                    $stmt->execute();
                }
                $stmt->close();
            }
            foreach ($plan['queue'] as $change) {
                $stmt = $mysqli->prepare('UPDATE ' . siteURLChangeQuoteName($prefix . 'encoder_queue') . ' SET ' . siteURLChangeQuoteName($change['column']) . ' = ? WHERE id = ?');
                $stmt->bind_param('si', $change['new'], $change['id']);
                $stmt->execute();
                $stmt->close();
            }
            $mysqli->commit();
        } catch (\Throwable $th) {
            $mysqli->rollback();
            siteURLChangeDiscardFile($prepared);
            throw new RuntimeException('the Encoder database was not changed (rolled back): ' . $th->getMessage());
        }
        $mysqli->close();
        echo 'Encoder database: ' . count($plan['streamers']) . ' user(s), ' . count((array) $plan['allowed']) . ' allowed list(s), ' . count($plan['queue']) . ' job value(s) changed.' . PHP_EOL;
    }
    if ($prepared) {
        siteURLChangeCommitFile($prepared, $backupDir);
        echo "Encoder configuration.php: webSiteRootURL = {$plan['newURL']}" . PHP_EOL;
    }
    if (!empty($plan['error'])) {
        throw new RuntimeException($plan['error']);
    }
}

// --- Apache / NGINX ---------------------------------------------------------------------------

function siteURLChangePrintServerPlan($plan, $skipped, $isRoot)
{
    if ($skipped) {
        echo '   skipped' . PHP_EOL;
        return;
    }
    if (empty($plan['files'])) {
        echo '   no file uses the old domain' . PHP_EOL;
        return;
    }
    foreach ($plan['files'] as $file => $lines) {
        echo "   {$file}" . PHP_EOL;
        foreach ($lines as $line) {
            echo "     line {$line['line']}: {$line['old']}" . PHP_EOL;
            echo "        becomes: {$line['new']}" . PHP_EOL;
        }
    }
    if (!$isRoot) {
        echo '   SKIPPED: run as root (sudo) to change these files' . PHP_EOL;
    }
}
