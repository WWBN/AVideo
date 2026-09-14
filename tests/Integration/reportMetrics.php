<?php
/**
 * Standalone numeric regression tests against MySQL/MariaDB.
 * Run: AVIDEO_REPORT_TEST_PORT=33317 php tests/Integration/reportMetrics.php
 * Uses localhost root with an empty password and connection-local TEMPORARY tables only.
 * Never loads the site's configuration or writes its persistent tables.
 */
if (PHP_SAPI !== 'cli' || !getenv('AVIDEO_REPORT_TEST_PORT')) {
    exit("Set AVIDEO_REPORT_TEST_PORT to an isolated local test database port.\n");
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli('127.0.0.1', 'root', '', 'mysql', (int) getenv('AVIDEO_REPORT_TEST_PORT'));
$db->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ONLY_FULL_GROUP_BY'");
class sqlDAL
{
    public static function readSql($sql, $formats = '', $values = [])
    {
        global $db;
        $statement = $db->prepare($sql);
        if ($formats !== '') { $statement->bind_param($formats, ...$values); }
        $statement->execute();
        $result = $statement->get_result();
        $statement->close();
        return $result;
    }
    public static function writeSql($sql, $formats = '', $values = [])
    {
        self::readSql($sql, $formats, $values);
        return true;
    }
    public static function fetchAllAssoc($result) { return $result->fetch_all(MYSQLI_ASSOC); }
    public static function fetchAssoc($result) { return $result->fetch_assoc(); }
    public static function close($result) { $result->free(); }
}
function __($text) { return $text; }
function humanTimingAgo(...$args) { return ''; }
class Video { public static function getPoster($id) { return ''; } }

// Execute the actual query methods without booting the application or mocking their SQL results.
function reportTestMethod($file, $name)
{
    $tokens = token_get_all(file_get_contents($file));
    for ($i = 0; $i < count($tokens); $i++) {
        if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION) { continue; }
        $j = $i + 1;
        while (is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) { $j++; }
        if (!is_array($tokens[$j]) || $tokens[$j][1] !== $name) { continue; }
        $code = 'public static ';
        $depth = 0;
        $started = false;
        for (; $i < count($tokens); $i++) {
            $token = $tokens[$i];
            $text = is_array($token) ? $token[1] : $token;
            $code .= $text;
            if ($token === '{' || (is_array($token) && in_array($token[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                $depth++; $started = true;
            } elseif ($token === '}') {
                $depth--;
                if ($started && $depth === 0) { return $code; }
            }
        }
    }
    throw new RuntimeException('Missing test method: ' . $name);
}
$root = dirname(__DIR__, 2);
require $root . '/objects/reportMetrics.php';
eval('class ReportTestRanking {' . reportTestMethod($root . '/plugin/VideosStatistics/VideosStatistics.php', 'getMostViewedVideosFromLastDays') . '}');
eval('class ReportTestLive { public static function getTableName(){return "live_transmitions_history";} public static function getSqlFromPost(){return "";}' .
    reportTestMethod($root . '/plugin/Live/Objects/LiveTransmitionHistory.php', 'recordAudienceSample') .
    reportTestMethod($root . '/plugin/Live/Objects/LiveTransmitionHistory.php', 'getAllFromUser') . '}');
$checks = 0;
function checkMetric($expected, $actual, $message)
{
    global $checks;
    if ($expected !== $actual) { throw new RuntimeException($message . ': expected ' . json_encode($expected) . ', got ' . json_encode($actual)); }
    $checks++;
}
foreach ([
    'videos' => 'id INT PRIMARY KEY, users_id INT, title TEXT, clean_title TEXT, filename TEXT, type VARCHAR(8), externalOptions TEXT, status CHAR(1), modified DATETIME, duration_in_seconds INT, likes INT, dislikes INT',
    'videos_statistics' => 'id INT AUTO_INCREMENT PRIMARY KEY, videos_id INT, `when` DATETIME, modified DATETIME, seconds_watching_video INT',
    'comments' => 'id INT PRIMARY KEY, videos_id INT, users_id INT, created DATETIME, modified DATETIME',
    'likes' => 'id INT AUTO_INCREMENT PRIMARY KEY, videos_id INT, `like` INT, created DATETIME, modified DATETIME',
    'comments_likes' => 'id INT AUTO_INCREMENT PRIMARY KEY, comments_id INT, `like` INT, created DATETIME, modified DATETIME',
    'subscribes' => 'id INT AUTO_INCREMENT PRIMARY KEY, users_id INT, status CHAR(1)',
    'live_transmitions_history' => 'id INT PRIMARY KEY, users_id INT, users_id_company INT, title TEXT, created DATETIME, modified DATETIME, finished DATETIME, total_viewers INT, max_viewers_sametime INT',
    'live_transmition_history_log' => 'id INT AUTO_INCREMENT PRIMARY KEY, live_transmitions_history_id INT, session_id VARCHAR(100), created DATETIME, modified DATETIME'
] as $table => $columns) { $db->query("CREATE TEMPORARY TABLE {$table} ({$columns}) ENGINE=InnoDB"); }
$db->query("INSERT INTO videos VALUES
    (1, 10, 'Published', 'published', 'v1', 'video', NULL, 'a', NOW(), 60, 999, 500),
    (2, 10, 'Unpublished', 'unpublished', 'v2', 'video', NULL, 'i', NOW(), 90, 400, 300),
    (3, 20, 'Other channel', 'other', 'v3', 'video', NULL, 'a', NOW(), 30, 0, 0)");
$db->query("INSERT INTO videos_statistics (videos_id, `when`, modified, seconds_watching_video) VALUES
    (1, '2026-01-01 00:00:00', NOW(), 10), (2, '2026-01-01 23:59:59', NOW(), 20),
    (3, '2026-01-01 12:00:00', NOW(), 30), (1, '2025-12-31 23:59:59', NOW(), 40),
    (1, '2026-01-02 00:00:00', NOW(), 50), (999, '2026-01-01 12:00:00', NOW(), 60)");
$from = '2026-01-01 00:00:00'; $to = '2026-01-01 23:59:59';
$videos = ReportMetrics::videoActivity(0, $from, $to);
$channels = ReportMetrics::videoActivity(0, $from, $to, true);
checkMetric(3, count($videos), 'Include unpublished videos and exclude orphan statistics');
checkMetric(3, (int) array_sum(array_column($videos, 'total_views')), 'Inclusive day boundaries');
checkMetric(60, (int) array_sum(array_column($videos, 'seconds_watching_video')), 'Watch seconds');
checkMetric(3, (int) array_sum(array_column($channels, 'total_views')), 'Channel and video views agree');
checkMetric(2, count(ReportMetrics::videoActivity(10, $from, $to)), 'Owner scope');
checkMetric([], ReportMetrics::videoActivity(99, $from, $to), 'Empty owner');
$db->query("INSERT INTO likes (videos_id, `like`, created, modified) VALUES (1,1,NOW(),NOW()),(2,-1,NOW(),NOW()),(1,0,NOW(),NOW()),(999,1,NOW(),NOW())");
$reactions = ReportMetrics::reactions(10);
checkMetric(1, (int) $reactions[0]['thumbsUp'], 'Recorded votes exclude inflated display counters, removed votes and orphans');
checkMetric(1, (int) $reactions[0]['thumbsDown'], 'Recorded dislikes');
$db->query("INSERT INTO comments VALUES (1,1,10,NOW(),NOW()),(2,3,20,NOW(),NOW())");
$db->query("INSERT INTO comments_likes (comments_id, `like`, created, modified) VALUES
    (1,1,'2025-01-01','2026-01-01'),(1,-1,'2026-01-01','2026-01-02'),(2,-1,'2026-01-01','2026-01-01')");
checkMetric(1, (int) ReportMetrics::reactions(10, true, $from, $to)[0]['thumbsUp'], 'Changed comment vote belongs to last-updated period');
checkMetric(0, (int) ReportMetrics::reactions(10, true, $from, $to)[0]['thumbsDown'], 'Exclude later vote change');
$db->query("INSERT INTO subscribes (users_id,status) VALUES (10,'a'),(10,'i'),(20,'a'),(0,'a')");
checkMetric(2, ReportMetrics::subscriptions(), 'Active recorded subscriptions');
checkMetric(1, ReportMetrics::subscriptions(10), 'Subscription owner scope');
// Ranking must use session start, not the recent modification of an old session.
$db->query("DELETE FROM videos_statistics");
$db->query("INSERT INTO videos_statistics (videos_id,`when`,modified,seconds_watching_video) VALUES
    (1,NOW(),NOW(),10),(1,NOW(),NOW(),10),(2,NOW(),NOW(),10),(3,NOW(),NOW(),10),
    (2,NOW()-INTERVAL 100 DAY,NOW(),10),(2,NOW()+INTERVAL 1 DAY,NOW(),10)");
$ranking = ReportTestRanking::getMostViewedVideosFromLastDays(10, 30, 10);
checkMetric(3, (int) $ranking->totalVideosViews, 'Ranking does not revive old views when watch time changes');
checkMetric(1, (int) $ranking->totalLikes, 'Likes counted once, per video');
checkMetric(1, (int) $ranking->totalDislikes, 'Dislikes counted once, per video');
checkMetric(1, (int) ReportTestRanking::getMostViewedVideosFromLastDays(0, 30, 1)->videos[0]->id, 'Rank before limiting');
// A report must not silently truncate at 10,000 videos or reactions.
$batch = [];
for ($i = 4; $i <= 10004; $i++) { $batch[] = "($i,10,'Bulk','bulk','v','video',NULL,'a',NOW(),1,0,0)"; }
$db->query('INSERT INTO videos VALUES ' . implode(',', $batch));
$db->query('INSERT INTO videos_statistics (videos_id,`when`,modified,seconds_watching_video) SELECT id,NOW(),NOW(),1 FROM videos WHERE id >= 4');
checkMetric(10004, count(ReportMetrics::videoActivity()), 'No 10,000-video truncation');
$db->query('INSERT INTO comments_likes (comments_id,`like`,created,modified) SELECT 1,-1,NOW(),NOW() FROM videos WHERE id >= 4');
checkMetric(10002, (int) ReportMetrics::reactions(10, true)[0]['thumbsDown'], 'No 10,000-dislike truncation');
$db->query("INSERT INTO live_transmitions_history VALUES
    (1,10,NULL,'Live',NOW(),NOW(),NULL,0,0),(2,20,NULL,'Finished',NOW(),NOW(),NOW(),0,0)");
$db->query("INSERT INTO live_transmition_history_log (live_transmitions_history_id,session_id,created,modified) VALUES
    (1,'a',NOW(),NOW()),(1,'a',NOW(),NOW()),(1,'b',NOW(),NOW()),
    (1,'old',NOW()-INTERVAL 1 HOUR,NOW()-INTERVAL 1 HOUR),(2,'other',NOW(),NOW())");
ReportTestLive::recordAudienceSample(1, 45);
$live = $db->query('SELECT * FROM live_transmitions_history WHERE id=1')->fetch_assoc();
checkMetric(2, (int) $live['max_viewers_sametime'], 'Live peak counts distinct recently active sessions');
checkMetric(3, (int) $live['total_viewers'], 'Total includes past viewers without duplicate sessions');
$db->query("UPDATE live_transmition_history_log SET modified=NOW()-INTERVAL 1 HOUR WHERE session_id='b'");
ReportTestLive::recordAudienceSample(1, 45);
checkMetric(2, (int) $db->query('SELECT max_viewers_sametime FROM live_transmitions_history WHERE id=1')->fetch_row()[0], 'A smaller sample cannot lower the peak');
ReportTestLive::recordAudienceSample(2, 45);
checkMetric(0, (int) $db->query('SELECT max_viewers_sametime FROM live_transmitions_history WHERE id=2')->fetch_row()[0], 'Do not change a finished broadcast');
checkMetric(false, ReportTestLive::recordAudienceSample(0), 'Invalid history ignored');
checkMetric(3, (int) ReportTestLive::getAllFromUser(10, true, false, 30, 'recent')[0]['total_viewers_from_history'], 'Live report deduplicates sessions');
echo "PASS: {$checks} numerical regression checks (temporary tables only).\n";
