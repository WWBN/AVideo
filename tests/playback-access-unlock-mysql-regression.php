<?php
// Local-only: evaluate each proposed purchase in a separate process and roll back every fixture.
if (PHP_SAPI !== 'cli' || getenv('AVIDEO_PLAYBACK_UNLOCK_MYSQL_TESTS') !== '1') {
    exit("Set AVIDEO_PLAYBACK_UNLOCK_MYSQL_TESTS=1 inside the local vlu.me container.\n");
}
$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = 'vlu.me';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 AVideo local unlock regression';
require dirname(__DIR__) . '/videos/configuration.php';
set_exception_handler(function ($error) { fwrite(STDERR, $error->getMessage() . PHP_EOL); exit(1); });
if (parse_url($global['webSiteRootURL'], PHP_URL_HOST) !== 'vlu.me' || !PlaybackAccess::isInstalled()
    || !AVideoPlugin::loadPluginIfEnabled('Subscription') || !AVideoPlugin::loadPluginIfEnabled('PayPerView')) {
    throw new RuntimeException('Only run against local vlu.me with both purchase plugins enabled');
}
function unlockMysqlInsert($sql, $types, $values)
{
    global $global;
    if (!sqlDAL::writeSql($sql, $types, $values)) { throw new RuntimeException('Could not create unlock fixture'); }
    return intval($global['mysqli']->insert_id);
}
function unlockMysqlPurchase($type, $plan, $user, $video)
{
    if ($type === 'subscription') {
        unlockMysqlInsert("INSERT INTO subscriptions (users_id,subscriptions_plans_id,status,good_until,created,modified) VALUES (?,?,'a',DATE_ADD(NOW(),INTERVAL 1 DAY),NOW(),NOW())", 'ii', [$user, $plan]);
    } elseif ($type === 'ppv') {
        unlockMysqlInsert("INSERT INTO ppv_plans_has_users (users_id,ppv_plans_id,status,expiration,php_expiration_time,created,modified) VALUES (?,?,'a',DATE_ADD(NOW(),INTERVAL 1 DAY),?,NOW(),NOW())", 'iii', [$user, $plan, time() + 86400]);
    } elseif ($type === 'rental') {
        // A rental of this title only, on its plan mapping (created when the scenario has none).
        $res = sqlDAL::readSql('SELECT id FROM ppv_plans_has_videos WHERE ppv_plans_id = ? AND videos_id = ? LIMIT 1', 'ii', [$plan, $video], true);
        $mapping = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        $mapping = $mapping ? intval($mapping['id']) : unlockMysqlInsert("INSERT INTO ppv_plans_has_videos (ppv_plans_id,videos_id,status,created,modified) VALUES (?,?,'a',NOW(),NOW())", 'ii', [$plan, $video]);
        unlockMysqlInsert("INSERT INTO ppv_plans_has_users_has_videos (users_id,ppv_plans_has_videos_id,expiration,status,created,modified) VALUES (?,?,DATE_ADD(NOW(),INTERVAL 1 DAY),'a',NOW(),NOW())", 'ii', [$user, $mapping]);
    }
}
$unlockMysqlScenarios = ['public', 'paid-only', 'visibility-b', 'paid-visibility-b', 'direct-ppv',
    'direct-ppv-other-visibility', 'existing-rental', 'existing-visibility', 'inactive-link', 'inactive-owner',
    'rental-option', 'rental-option-paid', 'rental-option-visibility-c', 'rental-option-inactive-link', 'rental-option-visibility-a'];
// rental:rental rents the title on the rental plan; ppv:rental buys that whole plan.
$unlockMysqlCandidates = ['none', 'subscription:a', 'subscription:b', 'ppv:a', 'ppv:rental', 'rental:rental'];
function unlockMysqlRentalOption($scenario)
{
    return strpos($scenario, 'rental-option') === 0;
}
if (($argv[1] ?? '') !== 'case') {
    foreach ($unlockMysqlScenarios as $scenario) {
        $results = [];
        foreach ($unlockMysqlCandidates as $candidate) {
            // With the PayPerView rental option the page sells the title's rental, not its whole rental plan.
            if ($candidate === 'ppv:rental' && unlockMysqlRentalOption($scenario)) {
                continue;
            }
            $process = proc_open([PHP_BINARY, __FILE__, 'case', $scenario, $candidate],
                [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (!is_resource($process)) { throw new RuntimeException('Could not start isolated unlock case'); }
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]); fclose($pipes[1]);
            $errors = stream_get_contents($pipes[2]); fclose($pipes[2]);
            $exit = proc_close($process);
            preg_match('/^UNLOCK_RESULT:(.+)$/m', $output, $resultLine);
            $result = json_decode($resultLine[1] ?? '', true);
            if ($exit !== 0 || !is_array($result)) {
                throw new RuntimeException('Isolated unlock case failed: ' . $scenario . '/' . $candidate . ' exit=' . $exit . ' output=' . strlen($output) . ' error=' . strlen($errors));
            }
            $results[$candidate] = $result;
        }
        $actual = [];
        foreach ($results as $candidate => $result) {
            if ($candidate !== 'none' && $result['canWatch']) { $actual[] = $candidate; }
        }
        $listed = $results['none']['offers'];
        sort($actual); sort($listed);
        if ($actual !== $listed || $results['none']['allowed']) {
            throw new RuntimeException('Offer mismatch: ' . $scenario . ' listed=' . json_encode($listed) . ' playable=' . json_encode($actual));
        }
        echo 'PASS: ', $scenario, ' offers=', json_encode($listed), ' (', count($results) - 1, ' purchases checked)', PHP_EOL;
    }
    exit("Unlock MySQL integration: passed\n");
}
$unlockMysqlScenario = $argv[2] ?? '';
$unlockMysqlCandidate = $argv[3] ?? '';
if (!in_array($unlockMysqlScenario, $unlockMysqlScenarios, true) || !in_array($unlockMysqlCandidate, $unlockMysqlCandidates, true)) {
    throw new InvalidArgumentException('Unknown unlock case');
}
$unlockMysqlBuyer = 0;
// Isolate both modes from the site's current setting; never change the saved plugin JSON.
AVideoPlugin::getObjectData('PayPerView')->allowRentalOfGroupRestrictedVideos = unlockMysqlRentalOption($unlockMysqlScenario);
mysqlBeginTransaction();
try {
    $prefix = 'unlock_test_' . bin2hex(random_bytes(6));
    $groups = [];
    foreach (['a', 'b', 'c'] as $group) {
        $groups[$group] = unlockMysqlInsert('INSERT INTO users_groups (group_name,created,modified) VALUES (?,NOW(),NOW())', 's', [$prefix . $group]);
    }
    $owner = unlockMysqlInsert("INSERT INTO users (user,password,isAdmin,status,created,modified) VALUES (?, 'not-a-login-password-hash',0,?,NOW(),NOW())", 'ss', [$prefix . '_owner', $unlockMysqlScenario === 'inactive-owner' ? 'i' : 'a']);
    $unlockMysqlBuyer = unlockMysqlInsert("INSERT INTO users (user,password,isAdmin,status,created,modified) VALUES (?, 'not-a-login-password-hash',0,'a',NOW(),NOW())", 's', [$prefix . '_buyer']);
    $category = unlockMysqlInsert('INSERT INTO categories (name,clean_name,parentId,created,modified) VALUES (?,?,0,NOW(),NOW())', 'ss', [$prefix, $prefix]);
    $paid = in_array($unlockMysqlScenario, ['paid-only', 'paid-visibility-b', 'direct-ppv', 'direct-ppv-other-visibility', 'inactive-link', 'rental-option-paid'], true) ? 1 : 0;
    $video = unlockMysqlInsert("INSERT INTO videos (title,clean_title,filename,duration,status,type,users_id,categories_id,only_for_paid,created,modified) VALUES (?,?,?,'00:00:01','a','video',?,?,?,NOW(),NOW())", 'sssiii', [$prefix, $prefix, $prefix, $owner, $category, $paid]);
    unlockMysqlInsert("INSERT INTO playback_access_rules (scope,scope_id,mode,groups_json,modified) VALUES ('video',?,'require',?,NOW())", 'is', [$video, json_encode([$groups['a'], $groups['b']])]);
    $plans = [];
    foreach (['a', 'b'] as $name) {
        $plans['subscription:' . $name] = unlockMysqlInsert("INSERT INTO subscriptions_plans (name,price,how_many_days,status,created,modified) VALUES (?,1,30,'a',NOW(),NOW())", 's', [$prefix . $name]);
        unlockMysqlInsert('INSERT INTO subscriptions_plans_has_users_groups (subscriptions_plans_id,users_groups_id,created,modified) VALUES (?,?,NOW(),NOW())', 'ii', [$plans['subscription:' . $name], $groups[$name]]);
    }
    foreach (['a', 'rental'] as $name) {
        $plans['ppv:' . $name] = unlockMysqlInsert("INSERT INTO ppv_plans (name,value,hours_valid,status,users_id,created,modified) VALUES (?,1,24,'a',?,NOW(),NOW())", 'si', [$prefix . $name, $owner]);
    }
    $plans['rental:rental'] = $plans['ppv:rental'];
    unlockMysqlInsert("INSERT INTO ppv_plans_has_users_groups (ppv_plans_id,users_groups_id,status,created,modified) VALUES (?,?,'a',NOW(),NOW())", 'ii', [$plans['ppv:a'], $groups['a']]);
    if (unlockMysqlRentalOption($unlockMysqlScenario)) {
        // The title is in the rental plan, like FlixHouse's 72-Hour Rental.
        unlockMysqlInsert('INSERT INTO ppv_plans_has_videos (ppv_plans_id,videos_id,status,created,modified) VALUES (?,?,?,NOW(),NOW())', 'iis', [$plans['ppv:rental'], $video, $unlockMysqlScenario === 'rental-option-inactive-link' ? 'i' : 'a']);
    }
    if (in_array($unlockMysqlScenario, ['visibility-b', 'paid-visibility-b', 'direct-ppv-other-visibility', 'existing-visibility', 'existing-rental', 'rental-option-visibility-c', 'rental-option-visibility-a'], true)) {
        $visibility = in_array($unlockMysqlScenario, ['visibility-b', 'paid-visibility-b'], true) ? $groups['b'] : ($unlockMysqlScenario === 'rental-option-visibility-a' ? $groups['a'] : $groups['c']);
        unlockMysqlInsert('INSERT INTO videos_group_view (videos_id,users_groups_id) VALUES (?,?)', 'ii', [$video, $visibility]);
    }
    if ($unlockMysqlScenario === 'existing-visibility') {
        unlockMysqlInsert('INSERT INTO users_has_users_groups (users_id,users_groups_id) VALUES (?,?)', 'ii', [$unlockMysqlBuyer, $groups['c']]);
    }
    if (in_array($unlockMysqlScenario, ['direct-ppv', 'direct-ppv-other-visibility', 'inactive-link'], true)) {
        unlockMysqlInsert('INSERT INTO ppv_plans_has_videos (ppv_plans_id,videos_id,status,created,modified) VALUES (?,?,?,NOW(),NOW())', 'iis', [$plans['ppv:a'], $video, $unlockMysqlScenario === 'inactive-link' ? 'i' : 'a']);
    }
    if ($unlockMysqlScenario === 'existing-rental') {
        $mapping = unlockMysqlInsert("INSERT INTO ppv_plans_has_videos (ppv_plans_id,videos_id,status,created,modified) VALUES (?,?,'a',NOW(),NOW())", 'ii', [$plans['ppv:rental'], $video]);
        unlockMysqlInsert("INSERT INTO ppv_plans_has_users_has_videos (users_id,ppv_plans_has_videos_id,expiration,status,created,modified) VALUES (?,?,DATE_ADD(NOW(),INTERVAL 1 DAY),'a',NOW(),NOW())", 'ii', [$unlockMysqlBuyer, $mapping]);
    }
    // Purchases precede every lookup of the buyer, including dynamic-group and plugin caches.
    if ($unlockMysqlCandidate !== 'none') {
        [$kind] = explode(':', $unlockMysqlCandidate);
        unlockMysqlPurchase($kind, $plans[$unlockMysqlCandidate], $unlockMysqlBuyer, $video);
    }
    $_SESSION['user'] = User::getUserFromID($unlockMysqlBuyer);
    $offers = [];
    foreach (PlaybackAccess::getUnlockOffers($video) as $offer) {
        $name = array_search($offer['id'], array_filter($plans, function ($key) use ($offer) { return strpos($key, $offer['type'] . ':') === 0; }, ARRAY_FILTER_USE_KEY), true);
        if ($name !== false) { $offers[] = $name; }
        else { throw new RuntimeException('Unexpected offer outside isolated fixtures'); }
    }
    $result = ['allowed' => PlaybackAccess::isAllowed($video, $unlockMysqlBuyer),
        'canWatch' => User::canWatchVideo($video, $unlockMysqlBuyer, true), 'offers' => $offers];
} finally {
    mysqlRollback();
    // deleteALLCache() can TRUNCATE cache tables and implicitly commit: never call it in this transaction.
    if ($unlockMysqlBuyer) { ObjectYPT::deleteCache('PayPerView::getGroupsFromUser/' . $unlockMysqlBuyer); }
    if (session_status() === PHP_SESSION_ACTIVE) { session_destroy(); }
}
echo 'UNLOCK_RESULT:', json_encode($result), PHP_EOL;
