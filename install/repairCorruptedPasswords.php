<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

// SECURITY: scans users.password for values that are not the app's hash shape
// (32 lowercase-hex chars, md5(whirlpool(sha1(...)))) and re-hashes them in place.
// This repairs accounts whose password was previously stored as plaintext by a
// since-fixed bug (see the hash-shape guard in User::save() and the self-heal in
// User::find()). Safe to run repeatedly - already-hashed rows are left untouched.
// The stored value itself is treated as the intended plaintext password (that is
// exactly what the corrupting bug wrote), so this restores the user's ability to
// log in with the same password, with no data loss.
$sql = "SELECT id, user, password FROM users WHERE password NOT REGEXP '^[a-f0-9]{32}$'";
$res = sqlDAL::readSql($sql);
$rows = [];
while ($row = sqlDAL::fetchAssoc($res)) {
    $rows[] = $row;
}
sqlDAL::close($res);

if (empty($rows)) {
    echo "No corrupted passwords found.\n";
    die();
}

echo count($rows) . " account(s) with a non-hash password found:\n";
foreach ($rows as $row) {
    $newHash = encryptPassword($row['password']);
    $ok = sqlDAL::writeSql("UPDATE users SET password = ? WHERE id = ?", "si", [$newHash, $row['id']]);
    if ($ok) {
        _error_log("SECURITY: repairCorruptedPasswords.php repaired a plaintext password for user={$row['user']} (id={$row['id']})", AVideoLog::$SECURITY);
        echo " - id={$row['id']} user={$row['user']}: repaired\n";
    } else {
        echo " - id={$row['id']} user={$row['user']}: FAILED to update\n";
    }
}
echo "Done. Affected users kept the same password, but you should still advise them\n";
echo "to change it, since it was sitting in the database as plaintext.\n";
die();
