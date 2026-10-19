<?php

namespace Tests\Fixtures\CatalogVisibility;

// Execute the real category/cache/policy code without loading the site's configuration or database.
$catalogFixtureRoot = dirname(__DIR__, 2);
$catalogFixtureSource = file_get_contents($catalogFixtureRoot . '/objects/category.php');
eval('namespace ' . __NAMESPACE__ . '; use stdClass; ' . substr($catalogFixtureSource, strpos($catalogFixtureSource, 'class Category')));
$catalogFixtureSource = file_get_contents($catalogFixtureRoot . '/objects/functions.php');
$catalogFixtureStart = strpos($catalogFixtureSource, 'function isToHidePrivateVideos()');
$catalogFixtureEnd = strpos($catalogFixtureSource, 'function ogSite()', $catalogFixtureStart);
eval('namespace ' . __NAMESPACE__ . '; ' . substr($catalogFixtureSource, $catalogFixtureStart, $catalogFixtureEnd - $catalogFixtureStart));
$catalogFixtureSource = file_get_contents($catalogFixtureRoot . '/plugin/API/API.php');
$catalogFixtureStart = strpos($catalogFixtureSource, '    private static function filterVideoSources(');
$catalogFixtureEnd = strpos($catalogFixtureSource, '    private static function getSubtitle(', $catalogFixtureStart);
eval('namespace ' . __NAMESPACE__ . '; use stdClass; class APIPlayback { '
    . substr($catalogFixtureSource, $catalogFixtureStart, $catalogFixtureEnd - $catalogFixtureStart)
    . ' public static function filter(&$row, $parameters, $secret) { return self::filterVideoSources($row, $parameters, $secret); } }');
$catalogFixtureSource = file_get_contents($catalogFixtureRoot . '/objects/Object.php');
$catalogFixtureStart = strpos($catalogFixtureSource, 'class VideosListCacheHandler');
$catalogFixtureEnd = strpos($catalogFixtureSource, 'class VideoCacheHandler', $catalogFixtureStart);
eval('namespace ' . __NAMESPACE__ . '; ' . substr($catalogFixtureSource, $catalogFixtureStart, $catalogFixtureEnd - $catalogFixtureStart));

class Fixture
{
    public static $categories = [];
    public static $videos = [];
    public static $layouts = [];
    public static $user = 0;
    public static $admin = false;
    public static $groups = [];
    public static $kids = false;
    public static $canWatch = false;
    public static $cache = [];
    public static $queries = [];

    public static function reset()
    {
        self::$categories = [];
        self::$videos = [];
        self::$layouts = ['Gallery' => false];
        self::$user = 0;
        self::$admin = false;
        self::$groups = [];
        self::$kids = false;
        self::$canWatch = false;
        self::$cache = [];
        self::$queries = [];
    }

    public static function category($id, $parent = 0)
    {
        self::$categories[$id] = ['id' => $id, 'parentId' => $parent, 'name' => 'Category ' . $id,
            'clean_name' => 'category-' . $id, 'description' => '', 'users_id' => 50,
            'private' => 0, 'suggested' => 1, 'order' => $id, 'iconClass' => 'fa fa-folder'];
    }

    public static function video($category, $group = 0, $status = 'a', $kids = true)
    {
        self::$videos[] = ['id' => count(self::$videos) + 1, 'categories_id' => $category, 'group' => $group,
            'status' => $status, 'made_for_kids' => $kids];
    }
}

class Config
{
    public function currentVersionLowerThen($version) { return false; }
    public function currentVersionGreaterThen($version) { return true; }
}
class User
{
    public static function getId() { return Fixture::$user; }
    public static function isLogged() { return Fixture::$user > 0; }
    public static function isAdmin() { return Fixture::$admin; }
    public static function getNameIdentificationById($id) { return 'Owner'; }
    public static function canWatchVideoWithAds($id) { return Fixture::$canWatch; }
}
class Permissions
{
    public static function canModerateVideos() { return Fixture::$admin; }
}
class Video
{
    public static function getUserGroupsCanSeeSQL($alias = '')
    {
        return Fixture::$admin ? '' : ' AND ' . $alias . 'visible_for_' . implode('_', Fixture::$groups) . ' = 1';
    }
    public static function getViewableStatus($unlisted) { return $unlisted ? ['a', 'u'] : ['a']; }
    public function __construct($title, $filename, $id) {}
    public function getVideo_password() { return password_hash('fixture-password', PASSWORD_DEFAULT); }
    public static function verifyVideoPassword($password, $stored) { return password_verify($password, $stored); }
}
class AVideoPlugin
{
    public static function isEnabledByName($name) { return false; }
    public static function getObjectDataIfEnabled($name)
    {
        return array_key_exists($name, Fixture::$layouts) ? (object)['hidePrivateVideos' => Fixture::$layouts[$name]] : false;
    }
}
class BootGrid
{
    public static function populateSortFromDataTablesOrder() {}
    public static function getSqlFromPost($fields, $prefix, $order, $doNotSearch = false)
    {
        $search = !$doNotSearch && !empty($_REQUEST['search']) ? " AND name = '" . $_REQUEST['search'] . "'" : '';
        return $search . $order . ' LIMIT ' . max(0, (getCurrentPage() - 1) * getRowCount()) . ', ' . getRowCount();
    }
}
class CacheHandler {}
class CategoryCacheHandler
{
    private $id;
    private $suffix;
    public function __construct($id) { $this->id = $id; }
    public function getCache($suffix, $lifetime = 0)
    {
        $this->suffix = $suffix;
        return Fixture::$cache[$this->id . ':' . $suffix] ?? null;
    }
    public function hasCache($suffix, $lifetime = 0)
    {
        $this->suffix = $suffix;
        return array_key_exists($this->id . ':' . $suffix, Fixture::$cache);
    }
    public function setCache($value)
    {
        Fixture::$cache[$this->id . ':' . $this->suffix] = $value;
        return true;
    }
}
class sqlDAL
{
    public static function readSql($sql, $types = '', $values = [], $refresh = false)
    {
        return (object)['rows' => self::readRows($sql, $values)];
    }
    private static function readRows($sql, $values)
    {
        Fixture::$queries[] = $sql;
        if (strpos($sql, 'SELECT COUNT(*) AS total') === 0) {
            return [['total' => count(Fixture::$categories), 'maxId' => count(Fixture::$categories), 'lastModified' => 'fixture']];
        }
        if (strpos($sql, 'SELECT count(id) as total FROM videos') === 0) {
            $total = 0;
            foreach (Fixture::$videos as $video) {
                if ($video['categories_id'] != $values[0] || $video['status'] !== 'a') {
                    continue;
                }
                if (self::filtersGroups($sql) && $video['group'] && !in_array($video['group'], Fixture::$groups)) {
                    continue;
                }
                if (strpos($sql, 'v.made_for_kids = 1') !== false && !$video['made_for_kids']) {
                    continue;
                }
                $total++;
            }
            return [['total' => $total]];
        }
        if (strpos($sql, 'SELECT * FROM videos') === 0) {
            preg_match('/categories_id IN \(([0-9,]+)\)/', $sql, $match);
            $categoryIds = explode(',', $match[1]);
            $rows = array_filter(Fixture::$videos, function ($video) use ($sql, $categoryIds) {
                return in_array($video['categories_id'], $categoryIds) && $video['status'] === 'a'
                    && (!self::filtersGroups($sql) || !$video['group'] || in_array($video['group'], Fixture::$groups))
                    && (strpos($sql, 'v.made_for_kids = 1') === false || $video['made_for_kids']);
            });
            return array_slice(array_reverse(array_values($rows)), 0, 1);
        }
        if (strpos($sql, 'FROM categories') !== false) {
            $rows = array_values(Fixture::$categories);
            if (strpos($sql, 'WHERE parentId=?') !== false) {
                $rows = array_values(array_filter($rows, function ($row) use ($values) {
                    return $row['parentId'] == $values[0] && $row['id'] != $values[1];
                }));
            } elseif (strpos($sql, 'WHERE id') !== false) {
                $rows = isset(Fixture::$categories[$values[0]]) ? [Fixture::$categories[$values[0]]] : [];
            }
            if (strpos($sql, 'AND parentId = 0') !== false) {
                $rows = array_values(array_filter($rows, function ($row) { return $row['parentId'] == 0; }));
            }
            if (strpos($sql, 'c.order >= 0') !== false) {
                $rows = array_values(array_filter($rows, function ($row) { return $row['order'] >= 0; }));
            }
            if (preg_match("/AND name = '([^']+)'/", $sql, $match)) {
                $rows = array_values(array_filter($rows, function ($row) use ($match) { return $row['name'] === $match[1]; }));
            }
            if (preg_match('/ LIMIT (\d+), (\d+)/', $sql, $match)) {
                $rows = array_slice($rows, (int)$match[1], (int)$match[2]);
            }
            return $rows;
        }
        throw new \RuntimeException('Unexpected fixture query: ' . $sql);
    }
    // Like MySQL: without the outer "v." alias the group subqueries compare their own columns and filter nothing.
    private static function filtersGroups($sql)
    {
        return strpos($sql, 'v.visible_for_') !== false;
    }
    public static function fetchAllAssoc($result) { return $result->rows; }
    public static function fetchAssoc($result) { return $result->rows[0] ?? false; }
    public static function close($rows) {}
}
function getRowCount() { return $_REQUEST['rowCount'] ?? 1000; }
function getCurrentPage() { return $_REQUEST['current'] ?? 1; }
function isForKidsSet() { return Fixture::$kids; }
function getRequestUniqueString() { return Fixture::$kids ? 'kids' : ''; }
function TimeLogStart($name) { return $name; }
function TimeLogEnd($name, $line) {}
function object_to_array($value) { return $value; }
function xss_esc_back($value) { return $value; }
function textToLink($value) { return $value; }
function __($value) { return $value; }
function isCommandLineInterface() { return false; }
