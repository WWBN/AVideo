<?php
// Opt-in, local-only HTTP regression. Committed disposable fixtures are removed in finally.
if (getenv('AVIDEO_PLAYBACK_HTTP_TESTS') !== '1' || PHP_SAPI !== 'cli') {
    exit("Set AVIDEO_PLAYBACK_HTTP_TESTS=1 inside the local vlu.me container.\n");
}
$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = 'vlu.me';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 AVideo local playback regression';
require dirname(__DIR__) . '/videos/configuration.php';
if (parse_url($global['webSiteRootURL'], PHP_URL_HOST) !== 'vlu.me' || !PlaybackAccess::isInstalled()) {
    throw new RuntimeException('Only run against local vlu.me after migration 30.3');
}
function playbackHttpAssert($condition, $label)
{
    if (!$condition) { echo 'FAIL: ', $label, PHP_EOL; throw new RuntimeException('FAIL: ' . $label); }
    echo 'PASS: ', $label, PHP_EOL;
}
function playbackHttpInsert($sql, $types, $values)
{
    global $global;
    if (!sqlDAL::writeSql($sql, $types, $values)) { throw new RuntimeException('Could not create HTTP fixture'); }
    return intval($global['mysqli']->insert_id);
}
function playbackHttpRequest($route, $parameters = [], $credentials = [])
{
    global $global;
    $client = curl_init($global['webSiteRootURL'] . $route . '?' . http_build_query($parameters));
    curl_setopt_array($client, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20,
        CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'], CURLOPT_REFERER => $global['webSiteRootURL'],
        CURLOPT_SSL_VERIFYPEER => shouldVerifySSLCertificates(), CURLOPT_SSL_VERIFYHOST => shouldVerifySSLCertificates() ? 2 : 0]);
    if ($credentials) { curl_setopt($client, CURLOPT_POSTFIELDS, http_build_query($credentials)); }
    $body = curl_exec($client);
    if ($body === false) { throw new RuntimeException('Local HTTP fixture request failed'); }
    return ['status' => curl_getinfo($client, CURLINFO_RESPONSE_CODE), 'body' => $body];
}
$playbackHttpPrefix = 'playback_http_x' . bin2hex(random_bytes(6));
// A run-specific key name also identifies the key handler's client cache files in the temp directory.
$playbackHttpKeyName = $playbackHttpPrefix . '_key';
$playbackHttpVideos = [];
$playbackHttpPlaylists = [];
$playbackHttpGroup = $playbackHttpUser = $playbackHttpCategory = 0;
$playbackHttpPassword = bin2hex(random_bytes(24));
$playbackHttpKey = random_bytes(16);
try {
    $playbackHttpMedia = '';
    $res = sqlDAL::readSql("SELECT filename FROM videos WHERE type = 'video' AND status = 'a' ORDER BY id DESC LIMIT 20");
    foreach (sqlDAL::fetchAllAssoc($res) as $candidate) {
        foreach (getVideosURL($candidate['filename']) as $file) {
            if (($file['format'] ?? '') === 'mp4' && is_file($file['path']) && filesize($file['path']) < 10485760) {
                $playbackHttpMedia = $file['path'];
                break 2;
            }
        }
    }
    sqlDAL::close($res);
    if (!$playbackHttpMedia) { throw new RuntimeException('A local MP4 under 10 MiB is required for HTTP source checks'); }
    $playbackHttpCategory = playbackHttpInsert('INSERT INTO categories (name,clean_name,parentId,created,modified) VALUES (?,?,0,NOW(),NOW())', 'ss', [$playbackHttpPrefix, $playbackHttpPrefix]);
    $playbackHttpGroup = playbackHttpInsert('INSERT INTO users_groups (group_name,created,modified) VALUES (?,NOW(),NOW())', 's', [$playbackHttpPrefix]);
    $playbackHttpUser = playbackHttpInsert("INSERT INTO users (user,password,isAdmin,status,emailVerified,created,modified) VALUES (?,?,0,'a',1,NOW(),NOW())", 'ss', [$playbackHttpPrefix, encryptPassword($playbackHttpPassword)]);
    sqlDAL::writeSql('INSERT INTO users_has_users_groups (users_id,users_groups_id) VALUES (?,?)', 'ii', [$playbackHttpUser, $playbackHttpGroup]);
    foreach ([$playbackHttpPrefix, $playbackHttpPrefix . '_normal'] as $filename) {
        $playbackHttpVideos[$filename] = playbackHttpInsert("INSERT INTO videos (title,clean_title,filename,duration,status,type,users_id,categories_id,created,modified) VALUES (?,?,?,'00:00:01','a','video',1,?,NOW(),NOW())", 'sssi', [$filename, $filename, $filename, $playbackHttpCategory]);
        if (!mkdir(getVideosDir() . $filename, 0755)) { throw new RuntimeException('Could not create HTTP media fixture'); }
        file_put_contents(getVideosDir() . $filename . '/' . $playbackHttpKeyName . '.key', $playbackHttpKey);
        if (!copy($playbackHttpMedia, getVideosDir() . $filename . '/' . $filename . '.mp4')) {
            throw new RuntimeException('Could not copy local HTTP media fixture');
        }
    }
    sqlDAL::writeSql('INSERT INTO playback_access_rules (scope,scope_id,mode,groups_json) VALUES (?,?,?,?)', 'siss', ['video', $playbackHttpVideos[$playbackHttpPrefix], 'require', json_encode([$playbackHttpGroup])]);
    $normal = playbackHttpRequest('plugin/VideoHLS/getToken.json.php', ['videos_id' => $playbackHttpVideos[$playbackHttpPrefix . '_normal']]);
    $normalToken = json_decode($normal['body'], true)['token'] ?? '';
    playbackHttpAssert($normal['status'] === 200 && $normalToken !== '', 'unconfigured video token still issued');
    $normalKey = playbackHttpRequest('plugin/VideoHLS/downloadProtection.php', ['filename' => $playbackHttpPrefix . '_normal', 'key' => $playbackHttpKeyName, 'token' => $normalToken]);
    playbackHttpAssert($normalKey['status'] === 200 && $normalKey['body'] === $playbackHttpKey, 'unconfigured key still delivered');
    $denied = playbackHttpRequest('plugin/VideoHLS/downloadProtection.php', ['filename' => $playbackHttpPrefix, 'key' => $playbackHttpKeyName]);
    playbackHttpAssert($denied['status'] === 403 && $denied['body'] !== $playbackHttpKey, 'visitor cannot obtain restricted key');
    $traversal = playbackHttpRequest('plugin/VideoHLS/downloadProtection.php', ['filename' => $playbackHttpPrefix . '_normal', 'key' => '../' . $playbackHttpPrefix . '/' . $playbackHttpKeyName, 'token' => $normalToken]);
    playbackHttpAssert($traversal['status'] === 403 && $traversal['body'] !== $playbackHttpKey, 'public video token cannot select another directory key');
    // The latest-video widget must always show something playable: make the restricted title the newest one.
    sqlDAL::writeSql('UPDATE videos SET created = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?', 'i', [$playbackHttpVideos[$playbackHttpPrefix]]);
    $latest = playbackHttpRequest('plugin/Live/latestOrLive.php', ['catName' => $playbackHttpPrefix]);
    playbackHttpAssert($latest['status'] === 200 && str_contains($latest['body'], $playbackHttpPrefix . '_normal.mp4')
        && !str_contains($latest['body'], $playbackHttpPrefix . '.mp4'), 'latestOrLive falls back to the newest permitted video instead of a 403');
    $member = playbackHttpRequest('plugin/VideoHLS/getToken.json.php', ['videos_id' => $playbackHttpVideos[$playbackHttpPrefix]], ['user' => $playbackHttpPrefix, 'pass' => $playbackHttpPassword]);
    $memberToken = json_decode($member['body'], true)['token'] ?? '';
    playbackHttpAssert($member['status'] === 200 && $memberToken !== '', 'authenticated member receives a token over HTTP');
    $memberKey = playbackHttpRequest('plugin/VideoHLS/downloadProtection.php', ['filename' => $playbackHttpPrefix, 'key' => $playbackHttpKeyName, 'token' => $memberToken]);
    playbackHttpAssert($memberKey['status'] === 200 && $memberKey['body'] === $playbackHttpKey, 'explicit member token works without a session cookie');
    $credentials = ['user' => $playbackHttpPrefix, 'pass' => $playbackHttpPassword];
    $watchURL = Video::getURL($playbackHttpVideos[$playbackHttpPrefix]);
    $allowedInfo = playbackHttpRequest('view/infoFromURL.php', ['url' => $watchURL], $credentials);
    playbackHttpAssert($allowedInfo['status'] === 200 && str_contains($allowedInfo['body'], $playbackHttpPrefix . '.mp4'), 'authorized infoFromURL has a real MP4 source');
    foreach (['public', 'favorite', 'watch_later'] as $status) {
        $playlist = playbackHttpInsert('INSERT INTO playlists (name,users_id,status,created,modified) VALUES (?,?,?,NOW(),NOW())', 'sis', [$playbackHttpPrefix, $playbackHttpUser, $status]);
        $playbackHttpPlaylists[$status] = $playlist;
        sqlDAL::writeSql('INSERT INTO playlists_has_videos (playlists_id,videos_id,`order`) VALUES (?,?,1)', 'ii', [$playlist, $playbackHttpVideos[$playbackHttpPrefix]]);
    }
    sqlDAL::writeSql('DELETE FROM users_has_users_groups WHERE users_id = ? AND users_groups_id = ?', 'ii', [$playbackHttpUser, $playbackHttpGroup]);
    $revoked = playbackHttpRequest('plugin/VideoHLS/downloadProtection.php', ['filename' => $playbackHttpPrefix, 'key' => $playbackHttpKeyName, 'token' => $memberToken]);
    playbackHttpAssert($revoked['status'] === 403 && $revoked['body'] !== $playbackHttpKey, 'membership revocation defeats a warmed key cache');
    $routes = [
        ['view/infoFromURL.php', ['url' => $watchURL], []],
        ['objects/playlistsVideos.json.php', ['playlists_id' => $playbackHttpPlaylists['public']], []],
        ['objects/playlistsFromUser.json.php', ['users_id' => $playbackHttpUser], []],
        ['objects/playlists.json.php', [], $credentials],
        ['plugin/API/get.json.php', ['APIName' => 'video', 'videos_id' => $playbackHttpVideos[$playbackHttpPrefix], 'noRelated' => 1], []],
        ['plugin/API/get.json.php', ['APIName' => 'program', 'playlists_id' => $playbackHttpPlaylists['public']], $credentials],
        ['plugin/API/get.json.php', ['APIName' => 'favorite'], $credentials],
        ['plugin/API/get.json.php', ['APIName' => 'watch_later'], $credentials],
    ];
    foreach ($routes as [$route, $parameters, $auth]) {
        $result = playbackHttpRequest($route, $parameters, $auth);
        playbackHttpAssert($result['status'] === 200 && json_decode($result['body']) !== null
            && str_contains($result['body'], $playbackHttpPrefix) && !str_contains($result['body'], $playbackHttpPrefix . '.mp4'),
            $route . ' ' . ($parameters['APIName'] ?? '') . ' retains metadata without restricted sources');
    }
    $catalogParameters = ['APIName' => 'video', 'searchPhrase' => $playbackHttpPrefix, 'noRelated' => 1, 'rowCount' => 10];
    playbackHttpRequest('plugin/API/get.json.php', $catalogParameters);
    $cachedCatalog = playbackHttpRequest('plugin/API/get.json.php', $catalogParameters);
    playbackHttpAssert($cachedCatalog['status'] === 200 && str_contains($cachedCatalog['body'], 'Cached response')
        && str_contains($cachedCatalog['body'], $playbackHttpPrefix) && !str_contains($cachedCatalog['body'], $playbackHttpPrefix . '.mp4'),
        'decoded cached API catalog is readable and contains no restricted source');
} finally {
    foreach ($playbackHttpPlaylists as $playlist) {
        sqlDAL::writeSql('DELETE FROM playlists_has_videos WHERE playlists_id = ?', 'i', [$playlist]);
        sqlDAL::writeSql('DELETE FROM playlists WHERE id = ? AND users_id = ?', 'ii', [$playlist, $playbackHttpUser]);
    }
    foreach ($playbackHttpVideos as $filename => $id) {
        sqlDAL::writeSql('DELETE FROM playback_access_rules WHERE scope = ? AND scope_id = ?', 'si', ['video', $id]);
        sqlDAL::writeSql('DELETE FROM videos WHERE id = ? AND filename = ?', 'is', [$id, $filename]);
        $directory = getVideosDir() . $filename;
        // Remove only this run's known files/directories, never a recursively computed path.
        if (dirname($directory) === rtrim(getVideosDir(), '/') && str_starts_with($filename, $playbackHttpPrefix) && is_dir($directory)) {
            unlink($directory . '/' . $playbackHttpKeyName . '.key');
            foreach (['.mp4', '.mp3'] as $extension) {
                if (is_file($directory . '/' . $filename . $extension)) { unlink($directory . '/' . $filename . $extension); }
            }
            rmdir($directory);
        }
    }
    if ($playbackHttpUser) {
        sqlDAL::writeSql('DELETE FROM users_has_users_groups WHERE users_id = ?', 'i', [$playbackHttpUser]);
        $user = new User($playbackHttpUser);
        if ($user->getUser() !== $playbackHttpPrefix || !$user->delete()) { throw new RuntimeException('HTTP test account cleanup failed'); }
    }
    if ($playbackHttpGroup) { sqlDAL::writeSql('DELETE FROM users_groups WHERE id = ? AND group_name = ?', 'is', [$playbackHttpGroup, $playbackHttpPrefix]); }
    if ($playbackHttpCategory) { sqlDAL::writeSql('DELETE FROM categories WHERE id = ? AND clean_name = ?', 'is', [$playbackHttpCategory, $playbackHttpPrefix]); }
    $res = sqlDAL::readSql('SELECT COUNT(*) AS total FROM users WHERE user = ?', 's', [$playbackHttpPrefix], true);
    playbackHttpAssert(intval(sqlDAL::fetchAssoc($res)['total']) === 0, 'disposable HTTP account removed');
    sqlDAL::close($res);
    foreach (array_keys($playbackHttpVideos) as $filename) {
        playbackHttpAssert(!is_dir(getVideosDir() . $filename), 'disposable HTTP media directory removed');
    }
    // downloadProtection.php records each delivered key in "<client>_<key>_v1.tmp" (same container temp directory).
    $playbackHttpKeyCache = sys_get_temp_dir() . '/*_' . $playbackHttpKeyName . '_v1.tmp';
    foreach (glob($playbackHttpKeyCache) ?: [] as $cacheFile) {
        unlink($cacheFile);
    }
    playbackHttpAssert(!glob($playbackHttpKeyCache), 'disposable HTTP key cache removed');
}
echo "Playback HTTP integration: passed\n";
