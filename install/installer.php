<?php
define('INSTALLER_PRODUCT', 'Streamer');
// Setup must work without loading the application's database configuration.
function installerRoot() { return str_replace('\\', '/', dirname(__DIR__)) . '/'; }
function installerConfigured() {
    clearstatcache();
    $file = installerRoot() . 'videos/configuration.php';
    return file_exists($file);
}
function installerSession() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Strict',
            'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    }
    if (empty($_SESSION['install_csrf_token'])) { $_SESSION['install_csrf_token'] = bin2hex(random_bytes(32)); }
}
function installerURL() {
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $path = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/install/index.php')));
    return ($https ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim($path, '/') . '/';
}
function installerQuote($value) {
    return "'" . str_replace("'", PHP_OS_FAMILY === 'Windows' ? "''" : "'\"'\"'", $value) . "'";
}
function installerPermissionHelp() {
    $target = is_dir(installerRoot() . 'videos') ? installerRoot() . 'videos' : rtrim(installerRoot(), '/');
    $root = installerQuote($target);
    if (PHP_OS_FAMILY === 'Windows') {
        return ['title' => 'PowerShell as administrator',
            'text' => 'Replace APACHE_ACCOUNT with the account running Apache (Services > Apache > Log On; if XAMPP was started manually, use whoami). Then try again.',
            'command' => 'icacls ' . $root . ' /grant "APACHE_ACCOUNT:(OI)(CI)M"'];
    }
    return ['title' => 'Server terminal', 'text' => 'Replace PHP_USER with the Apache/PHP-FPM account (for example, www-data). Grant access only to that account and try again.',
        'command' => "sudo apt-get install acl\nsudo setfacl -m u:PHP_USER:rwx " . $root];
}
function installerChecks() {
    $videos = installerRoot() . 'videos/';
    $checks = [
        ['label' => 'PHP 8.1 or later', 'ok' => version_compare(PHP_VERSION, '8.1', '>='), 'detail' => PHP_VERSION],
        ['label' => 'Database schema', 'ok' => is_readable(__DIR__ . '/database.sql'), 'detail' => 'install/database.sql'],
        ['label' => 'Configuration write access', 'ok' => is_dir($videos) ? is_writable($videos) : is_writable(installerRoot()), 'detail' => 'videos directory'],
        ['label' => 'Composer dependencies', 'ok' => is_readable(installerRoot() . 'vendor/autoload.php') && is_readable(installerRoot() . 'vendor/erusev/parsedown/Parsedown.php'), 'detail' => 'Run composer install if missing'],
    ];
    $checks[] = ['label' => 'Frontend assets', 'ok' => is_readable(installerRoot() . 'node_modules/jquery/dist/jquery.min.js'), 'detail' => 'Run npm install in the application directory.'];
    foreach (['mysqli', 'curl', 'gd', 'mbstring', 'zip', 'zlib', 'openssl'] as $extension) {
        $checks[] = ['label' => 'PHP ' . $extension, 'ok' => extension_loaded($extension), 'detail' => 'Enable in the web server php.ini'];
    }
    return $checks;
}
class InstallerFailure extends RuntimeException {
    public $help;
    public function __construct($message, array $help = []) { parent::__construct($message); $this->help = $help; }
}
function installerField(array $data, $key, $default = '') {
    $value = $data[$key] ?? $default;
    if (!is_string($value) || strlen($value) > 8192 || strpos($value, "\0") !== false) {
        throw new InstallerFailure('Invalid value for field ' . $key . '.');
    }
    return $value;
}
function installerValidateURL($url, $label) {
    $parts = parse_url($url);
    if (!filter_var($url, FILTER_VALIDATE_URL) || !$parts || !in_array($parts['scheme'] ?? '', ['http', 'https'], true) ||
        isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment']) || strlen($url) > 254) {
        throw new InstallerFailure($label . ': enter a complete HTTP/HTTPS URL without credentials, query parameters, or a fragment.');
    }
    return rtrim($url, '/') . '/';
}
function installerDatabaseMessage($code) {
    // MySQL client errors can use the operating system language.
    $messages = [
        1044 => 'The database account does not have access to this database.',
        1045 => 'Access denied. Check the database username and password.',
        1049 => 'The requested database does not exist.',
        1050 => 'A table with this name already exists.',
        1054 => 'A required database column is missing or incompatible.',
        1062 => 'A duplicate record conflicts with an existing database key.',
        1064 => 'The database rejected a SQL statement. Check the schema and server compatibility.',
        1142 => 'The database account does not have permission to perform this operation.',
        1143 => 'The database account does not have permission to access a required column.',
        1146 => 'A required database table is missing.',
        1227 => 'The database account is missing a required privilege.',
        1273 => 'The database server does not support the requested collation.',
        2002 => 'Unable to connect to the database server. Check the service, host, and port.',
        2003 => 'The database server refused the connection. Check the service, host, and port.',
        2005 => 'The database hostname could not be resolved.',
        2006 => 'The database server closed the connection.',
        2013 => 'The database connection was lost during the operation.',
    ];
    return 'MySQL/MariaDB (' . $code . '): ' . ($messages[$code] ?? 'The database operation failed. Review the database server logs and schema using the diagnostic command below.');
}
function installerDatabaseHelp($code, array $data) {
    $host = $data['databaseHost']; $port = $data['databasePort']; $db = $data['databaseName'];
    $user = str_replace(["\\", "'"], ["\\\\", "''"], $data['databaseUser']);
    $client = 'mysql';
    if (PHP_OS_FAMILY === 'Windows') {
        // Under Apache PHP_BINARY may be httpd.exe, so also inspect the loaded ini location.
        foreach ([dirname(php_ini_loaded_file() ?: PHP_BINARY, 2), dirname(PHP_BINARY, 2)] as $base) {
            $candidate = str_replace('\\', '/', $base) . '/mysql/bin/mysql.exe';
            if (is_file($candidate)) { $client = '& ' . installerQuote($candidate); break; }
        }
    }
    $connect = $client . ' --host=' . installerQuote($host) . ' --port=' . $port . ' --user=' . installerQuote($data['databaseUser']) . ' --password';
    if (in_array($code, [1044, 1045, 1142, 1143, 1227], true)) {
        return ['title' => 'MySQL / MariaDB access',
            'text' => 'Check your username and password. Test the connection using the command below. If permissions are missing, a database administrator must run the SQL; replace ACCOUNT_HOST with the MySQL account host (usually localhost for a local connection). The terminal will prompt for the password.',
            'command' => $connect,
            'sql' => "CREATE DATABASE IF NOT EXISTS `" . $db . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\nGRANT ALL PRIVILEGES ON `" . $db . "`.* TO '" . $user . "'@'ACCOUNT_HOST';"];
    }
    if (in_array($code, [2002, 2003, 2005, 2006, 2013], true)) {
        return ['title' => 'Check the database service',
            'text' => PHP_OS_FAMILY === 'Windows' ? 'Start MySQL in the XAMPP control panel. Check the host and port; for a remote server, also check the firewall. Test the port in PowerShell.' : 'Check the host and port. For a local database, check the service below; for a remote server, also check the firewall.',
            'command' => PHP_OS_FAMILY === 'Windows' ? 'Test-NetConnection -ComputerName ' . installerQuote($host) . ' -Port ' . $port : "sudo systemctl status mariadb\n# If the service is named mysql:\nsudo systemctl status mysql"];
    }
    return ['title' => 'Database diagnostics', 'text' => 'Open the SQL client and review the error and database structure. Do not remove tables containing data. Fix the cause, then try again.', 'command' => $connect . ' ' . installerQuote($db)];
}

function installerLanguages() {
    $global = [];
    require installerRoot() . 'objects/bcp47.php';
    $languages = [];
    foreach (glob(installerRoot() . 'locale/*.php') as $file) {
        $code = basename($file, '.php');
        if (isset($global['bcp47'][$code]['label'])) {
            $languages[$code] = $global['bcp47'][$code]['label'];
        }
    }
    asort($languages);
    return $languages;
}

function installerValidate(array $post, $testing = false) {
    $data = [];
    foreach (['databaseHost', 'databasePort', 'databaseName', 'databaseUser', 'databasePass'] as $key) {
        $data[$key] = installerField($post, $key);
        if ($key !== 'databasePass') { $data[$key] = trim($data[$key]); }
    }
    if ($data['databaseHost'] === '' || strlen($data['databaseHost']) > 253 ||
        !ctype_digit($data['databasePort']) || (int) $data['databasePort'] < 1 || (int) $data['databasePort'] > 65535 ||
        !preg_match('/^[a-zA-Z0-9_\-]{1,64}$/D', $data['databaseName']) ||
        $data['databaseUser'] === '' || strlen($data['databaseUser']) > 80) {
        throw new InstallerFailure('Check the database host, port (1–65535), username, and name. The database name accepts letters, numbers, hyphens, and underscores.');
    }
    $mode = $post['createTables'] ?? '2';
    if (is_int($mode)) { $post['createTables'] = (string) $mode; }
    $data['createTables'] = installerField($post, 'createTables', '2');
    if (!in_array($data['createTables'], ['0', '1', '2'], true)) { throw new InstallerFailure('Select a valid database setup option.'); }
    if ($testing) { return $data; }
    $path = installerField($post, 'systemRootPath', installerRoot());
    if (realpath($path) !== realpath(installerRoot())) { throw new InstallerFailure('The application path must point to this AVideo installation.'); }
    $data['systemRootPath'] = installerRoot();
    $data['webSiteRootURL'] = installerValidateURL(trim(installerField($post, 'webSiteRootURL')), 'Site URL');
    $data['webSiteTitle'] = trim(installerField($post, 'webSiteTitle'));
    $data['contactEmail'] = trim(installerField($post, 'contactEmail'));
    $data['mainLanguage'] = installerField($post, 'mainLanguage', 'en_US');
    if ($data['mainLanguage'] === 'en' || $data['mainLanguage'] === 'us') { $data['mainLanguage'] = 'en_US'; }
    if (!isset(installerLanguages()[$data['mainLanguage']])) { throw new InstallerFailure('Select an available site language.'); }
    if ($data['webSiteTitle'] === '' || preg_match('//u', $data['webSiteTitle']) !== 1 || preg_match_all('/./us', $data['webSiteTitle']) > 45) { throw new InstallerFailure('Enter a site title with 1 to 45 characters.'); }
    if (!filter_var($data['contactEmail'], FILTER_VALIDATE_EMAIL) || strlen($data['contactEmail']) > 254) { throw new InstallerFailure('Enter a valid contact email.'); }
    $data['systemAdminPass'] = installerField($post, 'systemAdminPass');
    if ($data['systemAdminPass'] === '') { throw new InstallerFailure('Enter an administrator password.'); }
    // Older CLI and form callers did not submit the confirmation field.
    if (isset($post['confirmSystemAdminPass']) && $data['systemAdminPass'] !== installerField($post, 'confirmSystemAdminPass')) { throw new InstallerFailure('The administrator passwords do not match.'); }
    $data['salt'] = installerField($post, 'salt');
    return $data;
}

function installerConfig(array $data) {
    $settings = [
        'configurationVersion' => 3.1, 'disableAdvancedConfigurations' => 0,
        'videoStorageLimitMinutes' => 0, 'disableTimeFix' => 0,
        'logfile' => installerRoot() . 'videos/avideo.log',
        'webSiteRootURL' => $data['webSiteRootURL'], 'systemRootPath' => installerRoot(),
        'salt' => $data['salt'] !== '' ? $data['salt'] : bin2hex(random_bytes(16)),
        'saltV2' => bin2hex(random_bytes(16)), 'enableDDOSprotection' => 1,
        'ddosMaxConnections' => 40, 'ddosSecondTimeout' => 5, 'strictDDOSprotection' => 0,
        'noDebug' => 0, 'webSiteRootPath' => parse_url($data['webSiteRootURL'], PHP_URL_PATH) ?: '/',
    ];
    $content = "<?php\n";
    foreach ($settings as $key => $value) {
        $content .= '$global[' . var_export($key, true) . '] = ' . var_export($value, true) . ";\n";
    }
    foreach (['mysqlHost' => 'databaseHost', 'mysqlPort' => 'databasePort', 'mysqlUser' => 'databaseUser', 'mysqlPass' => 'databasePass', 'mysqlDatabase' => 'databaseName'] as $variable => $key) {
        $content .= '$' . $variable . ' = ' . var_export($data[$key], true) . ";\n";
    }
    return $content . "\n// Do not change the bootstrap below.\nrequire_once \$global['systemRootPath'] . 'objects/include_config.php';\n";
}

function installerLog($exception, $stage) {
    // Bootstrap failures must also be reportable before Composer/application helpers exist.
    $message = 'AVideo installer: ' . $stage . ', ' . get_class($exception) . ' (' . $exception->getCode() . '), line ' . $exception->getLine();
    if (function_exists('_error_log')) { _error_log($message); }
    else { error_log($message); }
}

function installerSchemaTables() {
    $schema = file_get_contents(__DIR__ . '/database.sql');
    preg_match_all('/CREATE TABLE IF NOT EXISTS `([^`]+)`/', $schema, $matches);
    if (empty($matches[1])) { throw new InstallerFailure('The installation schema is missing or empty.'); }
    return $matches[1];
}

function installerCheckEmptyDatabase($mysqli) {
    $expected = installerSchemaTables();
    $result = $mysqli->query('SHOW FULL TABLES');
    while ($table = $result->fetch_row()) {
        if ($table[1] !== 'BASE TABLE' || !in_array($table[0], $expected, true) ||
            $mysqli->query('SELECT 1 FROM `' . str_replace('`', '``', $table[0]) . '` LIMIT 1')->num_rows > 0) {
            throw new InstallerFailure('This database already contains data or unrelated tables. No existing data was changed.',
                ['title' => 'Use an empty database', 'text' => 'Choose another database for a new site. To recover an existing site, restore its configuration.php from a backup instead of reinstalling.']);
        }
    }
    $result->free();
}

function installerImportSchema($mysqli) {
    // Reuse the install script's line-based import; stop at the first failed statement.
    // sqlDAL::executeFile logs and continues, which cannot provide installation atomicity.
    $statement = '';
    foreach (file(__DIR__ . '/database.sql') as $line) {
        if (strpos(ltrim($line), '--') === 0 || trim($line) === '') { continue; }
        $statement .= $line;
        if (substr(trim($line), -1) !== ';') { continue; }
        try { $mysqli->query($statement); }
        catch (mysqli_sql_exception $e) {
            // A retry can encounter these two indexes in an otherwise empty schema.
            if ($e->getCode() !== 1061 || !preg_match('/^ALTER TABLE `(category_type_cache|plugins)`\s+ADD (UNIQUE KEY `categoryId`|INDEX `plugin_status`)/', trim($statement))) { throw $e; }
        }
        $statement = '';
    }
}

function installerVerifySchema($mysqli) {
    foreach (installerSchemaTables() as $table) { $mysqli->query('SELECT 1 FROM `' . $table . '` LIMIT 0'); }
    foreach (['users' => 'id,user,email,password,created,modified,isAdmin', 'categories' => 'id,name,clean_name,description,created,modified',
        'configurations' => 'id,video_resolution,users_id,version,webSiteTitle,language,contactEmail,encoderURL,created,modified',
        'plugins' => 'id,uuid,status,created,modified,object_data,name,dirName,pluginVersion'] as $table => $columns) {
        $mysqli->query('SELECT ' . $columns . ' FROM `' . $table . '` LIMIT 0');
    }
    $result = $mysqli->query('SHOW TABLE STATUS');
    while ($table = $result->fetch_assoc()) {
        if (strcasecmp($table['Engine'] ?? '', 'InnoDB') !== 0) { throw new InstallerFailure('Installation requires InnoDB tables so initial records can be rolled back on failure.'); }
    }
    $result->free();
}

function installerSeed($mysqli, array $data) {
    // Keep the existing pre-configuration MySQLi connection: sqlDAL depends on a live
    // application, plugin loader and configuration that do not exist during setup.
    $hash = md5(hash('whirlpool', sha1($data['systemAdminPass'])));
    $stmt = $mysqli->prepare("INSERT INTO users (id,user,email,password,created,modified,isAdmin) VALUES (1,'admin',?,?,NOW(),NOW(),1)");
    $stmt->bind_param('ss', $data['contactEmail'], $hash);
    $stmt->execute(); $stmt->close();
    $mysqli->query("INSERT INTO categories (id,name,clean_name,description,created,modified) VALUES (1,'Default','default','',NOW(),NOW())");
    $encoder = is_dir(installerRoot() . 'Encoder') ? $data['webSiteRootURL'] . 'Encoder/' : 'https://encoder1.wwbn.net/';
    $stmt = $mysqli->prepare("INSERT INTO configurations (id,video_resolution,users_id,version,webSiteTitle,language,contactEmail,encoderURL,created,modified) VALUES (1,'858:480',1,'30.2',?,?,?,?,NOW(),NOW())");
    $stmt->bind_param('ssss', $data['webSiteTitle'], $data['mainLanguage'], $data['contactEmail'], $encoder);
    $stmt->execute(); $stmt->close();
    $mysqli->query("INSERT INTO plugins (uuid,status,created,modified,object_data,name,dirName,pluginVersion) VALUES ('a06505bf-3570-4b1f-977a-fd0e5cab205d','active',NOW(),NOW(),'','Gallery','Gallery','1.0')");
}

require_once __DIR__ . '/ubuntu-help-functions.php';
