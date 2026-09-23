<?php

// Shared update and health-check metadata; database migrations keep their own versioning.
function getAVideoUpdateGitInfo($root)
{
    $info = ['sha' => '', 'date' => '', 'branch' => '', 'tag' => ''];
    if (!file_exists($root . '/.git') || !function_exists('exec')) {
        return $info;
    }
    // Docker bind mounts can have a different owner from the PHP worker. Trust
    // only this installation for these read-only commands, not all repositories.
    $command = 'git -c ' . escapeshellarg('safe.directory=' . rtrim($root, '/\\')) . ' -C ' . escapeshellarg($root) . ' ';
    $output = [];
    exec($command . 'log -1 --format=%H%n%cI 2>' . (PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null'), $output, $code);
    if ($code !== 0 || !preg_match('/^[a-f0-9]{40}$/', $output[0] ?? '')) {
        return $info;
    }
    $info['sha'] = $output[0];
    $info['date'] = $output[1] ?? '';
    foreach (['branch' => 'symbolic-ref --quiet --short HEAD', 'tag' => 'describe --tags --exact-match HEAD'] as $key => $args) {
        $output = [];
        exec($command . $args . ' 2>' . (PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null'), $output, $code);
        if ($code === 0) {
            $info[$key] = $output[0] ?? '';
        }
    }
    return $info;
}

function getAVideoUpdateGitHubData($path)
{
    $key = 'avideoUpdateGitHub_' . md5($path);
    $cached = ObjectYPT::getCacheGlobal($key, 3600, true);
    if (is_object($cached)) {
        $cached = object_to_array($cached);
    }
    if (is_array($cached) && isset($cached['checked_at']) && time() - $cached['checked_at'] < 3600) {
        return $cached;
    }
    $data = json_decode((string) url_get_contents('https://api.github.com/repos/WWBN/AVideo/' . $path, '', 4), true);
    if (!is_array($data) || isset($data['message'])) {
        _error_log('Update page: GitHub version information unavailable for ' . $path);
        $data = null;
    }
    // Cache failed checks too, so a rate limit does not slow every page load.
    $result = ['data' => $data, 'checked_at' => time()];
    ObjectYPT::setCacheGlobal($key, $result);
    return $result;
}

function getAVideoUpdateCommitStatus($localSha, $remoteSha, $comparison = null)
{
    if (empty($localSha) || empty($remoteSha)) {
        return 'unknown';
    }
    if ($localSha === $remoteSha) {
        return 'identical';
    }
    if (is_array($comparison) && in_array($comparison['status'] ?? '', ['ahead', 'behind', 'diverged'], true)) {
        return $comparison['status'];
    }
    return 'unknown';
}

function getAVideoUpdateOverview($root, $fetch = null)
{
    $fetch = $fetch ?: 'getAVideoUpdateGitHubData';
    $local = getAVideoUpdateGitInfo($root);
    $releaseResponse = $fetch('releases/latest');
    $release = $releaseResponse['data'];
    if (empty($release['tag_name']) || !empty($release['draft']) || !empty($release['prerelease'])) {
        $release = null;
    }
    $releaseSha = '';
    if ($release) {
        // target_commitish can be a moving branch. Resolve the tag itself.
        $tag = $fetch('commits/' . rawurlencode($release['tag_name']));
        if (preg_match('/^[a-f0-9]{40}$/', $tag['data']['sha'] ?? '')) {
            $releaseSha = $tag['data']['sha'];
        }
    }
    $masterResponse = $fetch('commits/master');
    $master = $masterResponse['data'];
    if (!preg_match('/^[a-f0-9]{40}$/', $master['sha'] ?? '')) {
        $master = null;
    }
    $checkedAt = min($releaseResponse['checked_at'], $masterResponse['checked_at']);
    $comparisons = [];
    $statuses = [];
    foreach (['status' => $releaseSha, 'repository_status' => ($master['sha'] ?? '')] as $key => $remoteSha) {
        if ($remoteSha && $local['sha'] && $remoteSha !== $local['sha'] && !array_key_exists($remoteSha, $comparisons)) {
            // GitHub describes the HEAD relative to BASE: installed HEAD behind
            // the remote BASE means outdated. Never compare version numbers or dates.
            $response = $fetch('compare/' . $remoteSha . '...' . $local['sha'] . '?per_page=1');
            $comparisons[$remoteSha] = $response['data'];
            $checkedAt = min($checkedAt, $response['checked_at']);
        }
        $statuses[$key] = getAVideoUpdateCommitStatus($local['sha'], $remoteSha, $comparisons[$remoteSha] ?? null);
    }
    return [
        'local' => $local, 'release' => $release, 'release_sha' => $releaseSha,
        'master' => $master,
        'status' => $statuses['status'],
        'repository_status' => $statuses['repository_status'],
        'checked_at' => $checkedAt
    ];
}
