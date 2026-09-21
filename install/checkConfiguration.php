<?php
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/installer.php';
if (installerConfigured()) {
    echo json_encode(['error' => 'Setup is locked.', 'msg' => 'Installation is already complete.', 'success' => false]);
    return;
}
$steps = []; $stage = 'validation'; $mysqli = null; $lock = null; $temporary = null;
$committed = false; $transaction = false; $data = []; $installerSucceeded = false; $response = ['success' => false];
try {
    if (PHP_SAPI !== 'cli') {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405); header('Allow: POST');
            throw new InstallerFailure('Submit the setup form to continue.');
        }
        installerSession();
        if (!hash_equals($_SESSION['install_csrf_token'], installerField($_POST, 'install_csrf_token'))) {
            http_response_code(403);
            throw new InstallerFailure('Your install session has expired. Reload the page and try again.');
        }
        session_write_close();
    }
    if (installerConfigured()) { throw new InstallerFailure('Configuration already exists. Setup is locked. Open your site or restore its existing configuration from a backup.'); }
    $action = installerField($_POST, 'action', 'install');
    if (!in_array($action, ['test', 'install'], true)) { throw new InstallerFailure('Invalid setup action.'); }
    $data = installerValidate($_POST, $action === 'test');
    $stage = 'requirements';
    if (!extension_loaded('mysqli')) { throw new InstallerFailure('Enable the mysqli extension in the web server PHP configuration and restart PHP.'); }
    if ($action === 'install') {
        foreach (installerChecks() as $check) {
            if (!$check['ok']) { throw new InstallerFailure('Missing requirement: ' . $check['label'] . '.',
                installerRequirementHelp($check)); }
        }
        $steps[] = ['label' => 'Server requirements verified'];
    }
    $stage = 'connection';
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = mysqli_init();
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 8);
    $mysqli->real_connect($data['databaseHost'], $data['databaseUser'], $data['databasePass'], null, (int) $data['databasePort']);
    $mysqli->set_charset('utf8mb4');
    $steps[] = ['label' => 'MySQL / MariaDB connection confirmed'];
    $stage = 'database';
    $databaseExists = true;
    try { $mysqli->select_db($data['databaseName']); }
    catch (mysqli_sql_exception $e) {
        if ($e->getCode() !== 1049 || $data['createTables'] !== '2') { throw $e; }
        $databaseExists = false;
    }
    if ($action === 'test') {
        if ($databaseExists) { installerCheckEmptyDatabase($mysqli); }
        echo json_encode(['error' => false, 'msg' => $databaseExists ? 'Connection confirmed and the database contains no data. No changes were made.' : 'Connection confirmed. The database will be created during installation; creation privileges have not yet been tested.', 'steps' => $steps]);
        return;
    }
    $stage = 'configuration';
    $videos = installerRoot() . 'videos/';
    if (!is_dir($videos) && !@mkdir($videos, 0755, true)) { throw new InstallerFailure('Unable to create the videos directory.', installerPermissionHelp()); }
    $lock = @fopen($videos . '.installer.lock', 'c');
    if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) { throw new InstallerFailure('Another installation may be running. Wait for it to finish; if this persists, check write access.', installerPermissionHelp()); }
    if (installerConfigured()) { throw new InstallerFailure('Another installation has completed. Reload this page.'); }
    $temporary = $videos . '.configuration-' . bin2hex(random_bytes(12)) . '.php';
    $content = installerConfig($data);
    if (@file_put_contents($temporary, $content, LOCK_EX) !== strlen($content)) { throw new InstallerFailure('Unable to prepare configuration.php.', installerPermissionHelp()); }
    installerMatchDirectoryOwnership($temporary, $videos);
    if (PHP_OS_FAMILY !== 'Windows' && !chmod($temporary, 0600)) { throw new InstallerFailure('Unable to set configuration file permissions.', installerPermissionHelp()); }
    $stage = 'database';
    if (!$databaseExists) {
        $mysqli->query('CREATE DATABASE `' . $data['databaseName'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $mysqli->select_db($data['databaseName']);
    }
    installerCheckEmptyDatabase($mysqli);
    $steps[] = ['label' => 'Empty database verified'];
    $stage = 'tables';
    if ($data['createTables'] !== '0') { installerImportSchema($mysqli); }
    installerVerifySchema($mysqli);
    $steps[] = ['label' => 'All installation tables verified'];
    $stage = 'records';
    $mysqli->begin_transaction(); $transaction = true;
    installerSeed($mysqli, $data);
    $mysqli->commit(); $committed = true; $transaction = false;
    $steps[] = ['label' => 'Administrator, site settings, and Gallery created'];
    $stage = 'configuration';
    if (installerConfigured() || !@rename($temporary, $videos . 'configuration.php')) {
        $command = PHP_OS_FAMILY === 'Windows' ? 'Move-Item -LiteralPath ' . installerQuote($temporary) . ' -Destination ' . installerQuote($videos . 'configuration.php') : 'mv -n -- ' . installerQuote($temporary) . ' ' . installerQuote($videos . 'configuration.php');
        throw new InstallerFailure('The database is installed, but configuration.php could not be published. The prepared file has been preserved.',
            ['title' => 'Finish on the server', 'text' => 'Do not reinstall. Check that configuration.php does not already exist, then save the prepared configuration using this command and reload the page.', 'command' => $command]);
    }
    $temporary = null; $installerSucceeded = true;
    $steps[] = ['label' => 'configuration.php saved successfully'];
    echo json_encode($response = ['error' => false, 'success' => true, 'installed' => true, 'msg' => 'Installation complete. Sign in as admin using the password you chose.', 'url' => $data['webSiteRootURL'], 'steps' => $steps]);
} catch (Throwable $e) {
    if ($transaction) {
        try { $mysqli->rollback(); }
        catch (Throwable $rollbackError) { installerLog($rollbackError, 'rollback'); }
    }
    $message = 'Unable to complete this step. Check the PHP error log on the server.';
    $help = [];
    if ($e instanceof InstallerFailure) { $message = $e->getMessage(); $help = $e->help; }
    elseif ($e instanceof mysqli_sql_exception) {
        $message = installerDatabaseMessage($e->getCode());
        $help = installerDatabaseHelp($e->getCode(), $data);
    }
    installerLog($e, $stage);
    // Keep the legacy truthy error string and success flag for existing callers.
    echo json_encode(['error' => $message, 'success' => false, 'msg' => $message, 'stage' => $stage, 'steps' => $steps, 'help' => $help,
        'note' => !$committed && in_array($stage, ['tables', 'records'], true) ? 'Empty tables may remain. Fix the cause and retry; setup never deletes existing data.' : ''], JSON_INVALID_UTF8_SUBSTITUTE);
} finally {
    if ($temporary && !$committed && is_file($temporary) && !@unlink($temporary)) { installerLog(new RuntimeException('Temporary file cleanup failed'), 'cleanup'); }
    if (is_resource($lock)) { flock($lock, LOCK_UN); fclose($lock); }
    if ($mysqli) { $mysqli->close(); }
}
