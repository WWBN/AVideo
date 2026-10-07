<?php
// Optional integration check: run ONLY against a disposable MySQL/MariaDB instance.
// AVIDEO_TEST_MYSQL_PORT is required; this creates and drops its own random database.
$port = intval(getenv('AVIDEO_TEST_MYSQL_PORT'));
if (!$port) {
    throw new RuntimeException('Set AVIDEO_TEST_MYSQL_PORT to a disposable database server port');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$connection = new mysqli('127.0.0.1', 'root', '', '', $port);
$mysqlDatabase = $argv[2] ?? ('avideo_mutation_test_' . bin2hex(random_bytes(8)));
class AVideoLog { public static $ERROR = 1; }
function _error_log($message, $level = null) { fwrite(STDERR, $message . "\n"); }
class sqlDAL
{
    public static function readSql($sql, $formats = '', $values = [], $refresh = false)
    {
        global $connection;
        $stmt = $connection->prepare($sql);
        if ($formats !== '') $stmt->bind_param($formats, ...$values);
        $stmt->execute();
        return $stmt->get_result();
    }
    public static function fetchAssoc($res) { return $res->fetch_assoc(); }
    public static function close($res) { $res->free(); }
}
require_once dirname(__DIR__, 2) . '/objects/UserAccountMutationLock.php';
function verify($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}
if (($argv[1] ?? '') === 'promote') {
    $connection->select_db($mysqlDatabase);
    echo "ready\n";
    fflush(STDOUT);
    $lock = UserAccountMutationLock::acquire(7);
    verify($lock && !$lock->getIsAdmin(), 'Promotion lock or target read failed');
    $connection->query('UPDATE users SET isAdmin = 1 WHERE id = 7');
    unset($lock);
    echo "promoted\n";
    exit;
}
$connection->query("CREATE DATABASE `$mysqlDatabase`");
$connection->select_db($mysqlDatabase);
$process = null;
try {
    $connection->query('CREATE TABLE users (id INT PRIMARY KEY, isAdmin INT NOT NULL) ENGINE=InnoDB');
    $connection->query('INSERT INTO users VALUES (7, 0)');
    $lock = UserAccountMutationLock::acquire(7);
    $nested = UserAccountMutationLock::acquire(7);
    verify($lock && $nested, 'Could not acquire account lock');
    $process = proc_open([PHP_BINARY, __FILE__, 'promote', $mysqlDatabase], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    verify(is_resource($process), 'Could not start concurrent promotion');
    verify(trim(fgets($pipes[1])) === 'ready', 'Promotion worker failed to start');
    // The competing writer must still be waiting while either owner holds the lock.
    usleep(200000);
    verify($connection->query('SELECT isAdmin FROM users WHERE id = 7')->fetch_assoc()['isAdmin'] === '0', 'Promotion bypassed account lock');
    unset($nested);
    usleep(200000);
    verify($connection->query('SELECT isAdmin FROM users WHERE id = 7')->fetch_assoc()['isAdmin'] === '0', 'Nested scope released the outer lock');
    unset($lock);
    verify(trim(fgets($pipes[1])) === 'promoted', 'Promotion did not resume after lock release');
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    verify(proc_close($process) === 0 && $errors === '', 'Promotion worker failed: ' . $errors);
    $process = null;
    $lock = UserAccountMutationLock::acquire(7);
    verify($lock && $lock->getIsAdmin() === 1, 'Guard did not observe committed promotion');
    unset($lock);

    // A repeatable-read snapshot must not hide a committed admin promotion.
    $connection->query('UPDATE users SET isAdmin = 0 WHERE id = 7');
    $connection->begin_transaction();
    $connection->query('SELECT isAdmin FROM users WHERE id = 7');
    $other = new mysqli('127.0.0.1', 'root', '', $mysqlDatabase, $port);
    $other->query('UPDATE users SET isAdmin = 1 WHERE id = 7');
    $lock = UserAccountMutationLock::acquire(7);
    verify($lock && $lock->getIsAdmin() === 1, 'Existing transaction snapshot hid promotion');
    unset($lock);
    $connection->rollback();
    $other->close();
    echo "MySQL account mutation integration: passed\n";
} finally {
    if (is_resource($process)) {
        proc_terminate($process);
        proc_close($process);
    }
    unset($nested, $lock);
    $connection->rollback();
    $connection->query("DROP DATABASE `$mysqlDatabase`");
    $connection->close();
}
