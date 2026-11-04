<?php
// Opt-in, local-only integration test. All fixture rows are rolled back.
if (getenv('AVIDEO_PLAYBACK_MYSQL_TESTS') !== '1' || PHP_SAPI !== 'cli') {
    exit("Set AVIDEO_PLAYBACK_MYSQL_TESTS=1 inside the local vlu.me container.\n");
}
$_SERVER['HTTP_HOST'] = 'vlu.me';
$_SERVER['SERVER_NAME'] = 'vlu.me';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_USER_AGENT'] = 'AVideo local playback regression';
require dirname(__DIR__) . '/videos/configuration.php';
if (parse_url($global['webSiteRootURL'], PHP_URL_HOST) !== 'vlu.me' || !PlaybackAccess::isInstalled()) {
    throw new RuntimeException('Only run against local vlu.me after migration 30.3');
}
AVideoPlugin::loadPluginIfEnabled('PayPerView');
AVideoPlugin::loadPluginIfEnabled('VideoHLS');
function playbackFixtureInsert($sql, $formats, $values)
{
    global $global;
    if (!sqlDAL::writeSql($sql, $formats, $values)) {
        throw new RuntimeException('Could not insert playback fixture');
    }
    return intval($global['mysqli']->insert_id);
}
function playbackFixtureAssert($condition, $label)
{
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $label);
    }
    echo 'PASS: ', $label, PHP_EOL;
}
mysqlBeginTransaction();
try {
    $prefix = 'playback_test_' . bin2hex(random_bytes(4));
    $groupA = playbackFixtureInsert('INSERT INTO users_groups (group_name,created,modified) VALUES (?,NOW(),NOW())', 's', [$prefix . '_A']);
    $groupB = playbackFixtureInsert('INSERT INTO users_groups (group_name,created,modified) VALUES (?,NOW(),NOW())', 's', [$prefix . '_B']);
    $rootCategory = playbackFixtureInsert('INSERT INTO categories (name,clean_name,parentId,created,modified) VALUES (?,?,0,NOW(),NOW())', 'ss', [$prefix, $prefix]);
    $childCategory = playbackFixtureInsert('INSERT INTO categories (name,clean_name,parentId,created,modified) VALUES (?,?,?,NOW(),NOW())', 'ssi', [$prefix . '_child', $prefix . '_child', $rootCategory]);
    $normalCategory = playbackFixtureInsert('INSERT INTO categories (name,clean_name,parentId,created,modified) VALUES (?,?,0,NOW(),NOW())', 'ss', [$prefix . '_normal', $prefix . '_normal']);
    $users = [];
    foreach (['buyer','subscriber','allaccess','expired'] as $name) {
        $users[$name] = playbackFixtureInsert('INSERT INTO users (user,password,isAdmin,status,created,modified) VALUES (?,?,0,?,NOW(),NOW())', 'sss', [$prefix . '_' . $name, 'not-a-login-password-hash', 'a']);
    }
    sqlDAL::writeSql('INSERT INTO users_has_users_groups (users_id,users_groups_id) VALUES (?,?),(?,?)', 'iiii', [$users['subscriber'],$groupA,$users['allaccess'],$groupB]);
    $video = playbackFixtureInsert('INSERT INTO videos (title,clean_title,filename,duration,status,type,users_id,categories_id,only_for_paid,created,modified) VALUES (?,?,?,?,?,?,?,?,1,NOW(),NOW())', 'ssssssii', [$prefix,$prefix,$prefix,'00:00:01','a','video',1,$childCategory]);
    $normalVideo = playbackFixtureInsert('INSERT INTO videos (title,clean_title,filename,duration,status,type,users_id,categories_id,created,modified) VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())', 'ssssssii', [$prefix.'_normal',$prefix.'_normal',$prefix.'_normal','00:00:01','a','video',1,$normalCategory]);
    sqlDAL::writeSql('INSERT INTO videos_group_view (videos_id,users_groups_id) VALUES (?,?),(?,?)', 'iiii', [$video,$groupA,$video,$groupB]);
    $plan = playbackFixtureInsert('INSERT INTO ppv_plans (name,value,hours_valid,status,users_id,created,modified) VALUES (?,1,72,?,1,NOW(),NOW())', 'ss', [$prefix,'a']);
    $mapping = playbackFixtureInsert('INSERT INTO ppv_plans_has_videos (ppv_plans_id,videos_id,status,created,modified) VALUES (?,?,?,NOW(),NOW())', 'iis', [$plan,$video,'a']);
    playbackFixtureInsert('INSERT INTO ppv_plans_has_users_has_videos (users_id,ppv_plans_has_videos_id,value_paid,expiration,status,watched_times,created,modified) VALUES (?,?,1,DATE_ADD(NOW(),INTERVAL 72 HOUR),?,0,NOW(),NOW())', 'iis', [$users['buyer'],$mapping,'a']);
    sqlDAL::writeSql('INSERT INTO playback_access_rules (scope,scope_id,mode,groups_json) VALUES (?,?,?,?)', 'siss', ['category',$rootCategory,'require',json_encode([$groupA,$groupB])]);
    playbackFixtureAssert(PPV_Plans_Users_Videos::userCanWatchVideo($users['buyer'],$video) === 2, 'real PPV rental exists');
    playbackFixtureAssert(!User::canWatchVideo($video,$users['buyer'],true), 'PPV cannot bypass inherited required groups');
    playbackFixtureAssert(!User::canWatchVideoWithAds($video,$users['buyer'],true), 'ads cannot bypass inherited required groups');
    playbackFixtureAssert(!PlaybackAccess::isAllowed($video,0), 'visitor denied');
    playbackFixtureAssert(User::canWatchVideoWithAds($video,$users['subscriber'],true), 'allowed subscriber group');
    playbackFixtureAssert(User::canWatchVideoWithAds($video,$users['allaccess'],true), 'allowed alternate group');
    playbackFixtureAssert(!PlaybackAccess::isAllowed($video,$users['expired']), 'account without current group denied');
    playbackFixtureAssert(PlaybackAccess::isAllowed($normalVideo,$users['buyer']), 'unconfigured catalog unchanged');
    playbackFixtureAssert(VideoHLS::getToken($video) === '', 'unauthorized HLS token issuance denied');
    $identity = json_decode(decryptString(VideoHLS::getToken($normalVideo)));
    $identity->users_id = $users['subscriber'];
    $identity->videos_id = $video;
    $token = encryptString(json_encode($identity));
    $_REQUEST['filename'] = $prefix;
    playbackFixtureAssert(VideoHLS::canAccessMandatoryRule($video,$token), 'native HLS accepts signed authorized user');
    $identity->videos_id = $normalVideo;
    playbackFixtureAssert(!VideoHLS::canAccessMandatoryRule($video,encryptString(json_encode($identity))), 'HLS token for another video denied');
    playbackFixtureAssert(PlaybackAccess::isRestricted($video) && !PlaybackAccess::isRestricted($normalVideo), 'feeds leave out restricted titles only');
    // The fixture videos have no media files: borrow an existing local video that has a playable file.
    $mediaVideo = 0;
    $res = sqlDAL::readSql("SELECT id, filename FROM videos WHERE status = 'a' AND type = 'video' ORDER BY id DESC LIMIT 20");
    foreach (sqlDAL::fetchAllAssoc($res) as $candidate) {
        foreach (getVideosURL($candidate['filename']) as $candidateFile) {
            if (($candidateFile['type'] ?? '') === 'video') {
                $mediaVideo = intval($candidate['id']);
                break 2;
            }
        }
    }
    if ($mediaVideo) {
        sqlDAL::writeSql('INSERT INTO playback_access_rules (scope,scope_id,mode,groups_json) VALUES (?,?,?,?)', 'siss', ['video',$mediaVideo,'require',json_encode([$groupA])]);
        $mediaPlaylist = playbackFixtureInsert("INSERT INTO playlists (name,users_id,status,created,modified) VALUES (?,1,'public',NOW(),NOW())", 's', [substr($prefix, 0, 45)]);
        sqlDAL::writeSql('INSERT INTO playlists_has_videos (playlists_id,videos_id,`order`) VALUES (?,?,1)', 'ii', [$mediaPlaylist,$mediaVideo]);
        $playbackPreviousDir = getcwd();
        $_REQUEST = $_GET = ['playlists_id' => $mediaPlaylist];
        chdir(dirname(__DIR__) . '/objects'); // the endpoint uses cwd-relative includes
        ob_start();
        include dirname(__DIR__) . '/objects/playlistsVideos.json.php';
        $playlistResponse = json_decode(ob_get_clean(), true);
        chdir($playbackPreviousDir);
        $playlistRow = $playlistResponse[0] ?? [];
        $playlistTypes = array_unique(array_column($playlistRow['VideoUrl'] ?? [], 'type'));
        playbackFixtureAssert(!empty($playlistRow) && $playlistRow['firstVideo'] === '' && array_diff($playlistTypes, ['image']) === [], 'visitor playlist response keeps images but no media file');
        playbackFixtureAssert(empty($playlistRow['videos']) && empty($playlistRow['videoLink']) && empty($playlistRow['videoDownloadedLink']), 'visitor playlist response removes every original playback field');
    } else {
        echo 'SKIP: no local video with a playable file for the playlist endpoint check', PHP_EOL;
    }
    echo "Playback MySQL integration: passed\n";
} finally {
    mysqlRollback();
}
