<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once dirname(__FILE__) . '/../videos/configuration.php';
}

require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';

class Category
{

    protected $properties = [];
    private $id;
    private $name;
    private $clean_name;
    private $description;
    private $iconClass;
    private $nextVideoOrder;
    private $parentId;
    private $type;
    private $users_id;
    private $private;
    private $allow_download;
    private $order;
    private $suggested;

    public function getSuggested()
    {
        return empty($this->suggested) ? 0 : 1;
    }

    public function setSuggested($suggested)
    {
        $this->suggested = empty($suggested) ? 0 : 1;
    }

    public function getOrder()
    {
        return intval($this->order);
    }

    public function setOrder($order)
    {
        $this->order = intval($order);
    }

    public function getUsers_id()
    {
        if (empty($this->users_id)) {
            $this->users_id = User::getId();
        }
        return $this->users_id;
    }

    public function getPrivate()
    {
        return $this->private;
    }

    public function setUsers_id($users_id)
    {
        // only admin can change owner
        if (!empty($this->users_id) && !User::isAdmin()) {
            return false;
        }

        $this->users_id = intval($users_id);
    }

    public function setPrivate($private)
    {
        $this->private = empty($private) ? 0 : 1;
    }

    public function setName($name)
    {
        $this->name = _substr($name, 0, 45);
    }

    public function setClean_name($clean_name)
    {
        $clean_name = preg_replace('/\W+/', '-', strtolower(cleanString($clean_name)));
        $this->clean_name = _substr($clean_name, 0, 45);
    }

    public function setNextVideoOrder($nextVideoOrder)
    {
        $this->nextVideoOrder = $nextVideoOrder;
    }

    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function __construct($id, $name = '')
    {
        if (empty($id)) {
            // get the category data from category and pass
            $this->name = $name;
        } else {
            $this->id = $id;
            // get data from id
            $this->load($id);
        }
    }

    public function load($id, $refreshCache = false)
    {
        $row = self::getCategory($id);
        if (empty($row)) {
            return false;
        }
        foreach ($row as $key => $value) {
            @$this->$key = $value;
            //$this->properties[$key] = $value;
        }
        return true;
    }

    public function loadSelfCategory()
    {
        $this->load($this->id);
    }

    public function save($allowOfflineUser = false)
    {
        global $global;

        if (!$allowOfflineUser && !self::canCreateCategory()) {
            return false;
        }

        if (!$allowOfflineUser && !empty($this->id) && !self::userCanEditCategory($this->id)) {
            return false;
        }

        if (empty($this->users_id)) {
            $this->users_id = User::getId();
        }

        $this->clean_name = self::fixCleanTitle($this->clean_name, 1, $this->id);

        // check if clean name exists
        $exists = $this->getCategoryByName($this->clean_name);
        if (!empty($exists) && $exists['id'] !== $this->id) {
            $this->clean_name .= uniqid();
        }

        $this->nextVideoOrder = intval($this->nextVideoOrder);
        $this->parentId = intval($this->parentId);
        if (!empty($this->id)) {
            $sql = "UPDATE categories SET "
                . "name = ?,"
                . "clean_name = ?,"
                . "description = ?,"
                . "nextVideoOrder = ?,"
                . "parentId = ?,"
                . "iconClass = ?,"
                . "users_id = ?,"
                . "suggested = ?,"
                . "`private` = ?, allow_download = ?, `order` = ?, modified = now() WHERE id = ?";
            $format = "sssiisiiiiii";
            $values = [$this->name, $this->clean_name, $this->description, intval($this->nextVideoOrder), $this->parentId, $this->getIconClass(), $this->getUsers_id(), $this->getSuggested(), $this->getPrivate(), $this->getAllow_download(), $this->getOrder(), $this->id];
        } else {
            $sql = "INSERT INTO categories ( "
                . "name,"
                . "clean_name,"
                . "description,"
                . "nextVideoOrder,"
                . "parentId,"
                . "iconClass, "
                . "users_id, "
                . "suggested, "
                . "`private`, allow_download, `order`, created, modified) VALUES (?, ?,?,?,?,?,?,?,?,?,?,now(), now())";
            $format = "sssiisiiiii";
            $values = [$this->name, $this->clean_name, $this->description, intval($this->nextVideoOrder), $this->parentId, $this->getIconClass(), $this->getUsers_id(), $this->getSuggested(), $this->getPrivate(), $this->getAllow_download(), $this->getOrder()];
        }
        $insert_row = sqlDAL::writeSql($sql, $format, $values);
        if ($insert_row) {
            //Category::deleteCategoryCache();
            if (empty($this->id)) {
                $id = $insert_row;
            } else {
                $id = $this->id;
            }
            $cacheHandler = new CategoryCacheHandler($id);
            $cacheHandler->deleteCache();
            // delete the select
            $cacheHandler = new CategoryCacheHandler(0);
            $cacheHandler->deleteCache();
            self::deleteOGImage($id);
            return $id;
        } else {
            //die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            return false;
        }
    }

    /**
     *
     * @param string $clean_title
     * @param int $count
     * @param int $id
     * @param string $original_title
     * @return string
     */
    public static function fixCleanTitle($clean_title, $count, $id, $original_title = "")
    {
        global $global;

        if (empty($original_title)) {
            $original_title = $clean_title;
        }

        $sql = "SELECT * FROM categories WHERE clean_name = '{$clean_title}' ";
        if (!empty($id)) {
            $sql .= " AND id != {$id} ";
        }
        $sql .= " LIMIT 1";
        $res = sqlDAL::readSql($sql, "", [], true);
        $cleanTitleExists = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!empty($cleanTitleExists)) {
            $new_cleanTitle = $original_title . "-" . $count;
            return self::fixCleanTitle($new_cleanTitle, $count + 1, $id, $original_title);
        }
        return $clean_title;
    }

    public static function deleteAssets($categories_id)
    {
        $dirPaths = self::getCategoryDirPath($categories_id);
        return rrmdir($dirPaths['path']);
    }

    /*
      static function getCategoryType($categoryId) {
      global $global;
      $sql = "SELECT * FROM `category_type_cache` WHERE categoryId = ?;";
      $res = sqlDAL::readSql($sql, "i", array($categoryId));
      $data = sqlDAL::fetchAssoc($res);
      sqlDAL::close($res);
      if ($res) {
      if (!empty($data)) {
      return $data;
      } else {
      return array("categoryId" => "-1", "type" => "0", "manualSet" => "0");
      }
      } else {
      return array("categoryId" => "-1", "type" => "0", "manualSet" => "0");
      }
      }
     *
     */

    public static function getCategory($id)
    {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM categories WHERE id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", [$id]);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($result) {
            $result['name'] = xss_esc_back($result['name']);
        }
        return ($res) ? $result : false;
    }

    public static function getCategoryLink($id)
    {
        $cat = new Category($id);
        return self::getCategoryLinkFromName($cat->getClean_name());
    }

    public static function getCategoryLinkFromName($clean_name)
    {
        global $global;
        return "{$global['webSiteRootURL']}cat/{$clean_name}";
    }

    public function getLink()
    {
        return self::getCategoryLinkFromName($this->getClean_name());
    }

    public static function getCategoryByName($name)
    {
        global $global;
        $sql = "SELECT * FROM categories WHERE clean_name = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", [$name]);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($result) {
            $result['name'] = xss_esc_back($result['name']);
            $result['description_html'] = textToLink(htmlentities("{$result['description']}"));
        }
        //var_dump($sql,$name, $result);exit;
        return ($res) ? $result : false;
    }

    public static function getOrCreateCategoryByName($name)
    {
        $cat = self::getCategoryByName($name);
        if (empty($cat)) {
            $obj = new Category(0);
            $obj->setName($name);
            $obj->setClean_name($name);
            $obj->setDescription("");
            $obj->setIconClass("");
            $obj->setNextVideoOrder(0);
            $obj->setParentId(0);

            $id = $obj->save();
            return self::getCategoryByName($name);
        }
        return $cat;
    }

    public static function getCategoryDefault()
    {
        global $global;
        $sql = "SELECT * FROM categories ORDER BY id ASC LIMIT 1";
        $res = sqlDAL::readSql($sql);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($result) {
            $result['name'] = xss_esc_back($result['name']);
        }
        return ($res) ? $result : false;
    }

    public static function getSiteCategoryDefaultID()
    {
        $obj = AVideoPlugin::getObjectDataIfEnabled("PredefinedCategory");
        $id = false;
        if ($obj) {
            $id = $obj->defaultCategory;
        } else {
            $row = self::getCategoryDefault();
            if ($row) {
                $id = $row['id'];
            }
        }
        return $id;
    }

    public static function deleteCategoryCache()
    {
        return ObjectYPT::deleteALLCache();
        //_error_log("deleteCategoryCache: {$cacheDir} = " . json_encode($rrmdir));
    }

    public static function getAllCategories($filterCanAddVideoOnly = false, $onlyWithVideos = false, $onlySuggested = false, $sameUserGroupAsMe = false)
    {
        global $global, $config;
        if ($config->currentVersionLowerThen('8.4')) {
            return false;
        }
        $sql = "SELECT * FROM categories c WHERE 1=1 ";
        if (!empty($_GET['parentsOnly'])) {
            $sql .= "AND parentId = 0 ";
        }
        if ($onlySuggested) {
            $sql .= "AND suggested = 1 ";
        }

        if (!empty($_REQUEST['doNotShowCats'])) {
            $doNotShowCats = $_REQUEST['doNotShowCats'];
            if (!is_array($_REQUEST['doNotShowCats'])) {
                $doNotShowCats = array($_REQUEST['doNotShowCats']);
            }
            foreach ($doNotShowCats as $key => $value) {
                $doNotShowCats[$key] = str_replace("'", '', $value);
            }
            $sql .= " AND (c.clean_name NOT IN ('" . implode("', '", $doNotShowCats) . "') )";
        }

        if ($filterCanAddVideoOnly && !User::isAdmin()) {
            if (is_int($filterCanAddVideoOnly)) {
                $users_id = $filterCanAddVideoOnly;
            } else {
                $users_id = User::getId();
            }

            if ($config->currentVersionGreaterThen('6.1')) {
                $sql .= " AND (private=0 OR users_id = '{$users_id}') ";
            }
        }

        if ($onlyWithVideos) {
            $sql .= " AND ((SELECT count(*) FROM videos v where 1=1 ";
            if (isForKidsSet()) {
                $sql .= " AND v.made_for_kids = 1 ";
            }
            $sql .= " AND v.categories_id = c.id OR categories_id IN (SELECT id from categories where parentId = c.id AND id != c.id)) > 0  ";
            if (AVideoPlugin::isEnabledByName("Live")) {
                $sql .= " OR "
                    . " ("
                    . " SELECT count(*) FROM live_transmitions lt where "
                    . " (lt.categories_id = c.id OR lt.categories_id IN (SELECT id from categories where parentId = c.id AND id != c.id))"
                    //. " AND lt.id = (select id FROM live_transmitions lt2 WHERE lt.users_id = lt2.users_id ORDER BY CREATED DESC LIMIT 1 )"
                    . " ) > 0  ";
            }
            if (AVideoPlugin::isEnabledByName("LiveLinks")) {
                $sql .= " OR "
                    . " ("
                    . " SELECT count(*) FROM LiveLinks ll where "
                    . " (ll.categories_id = c.id OR ll.categories_id IN (SELECT id from categories where parentId = c.id AND id != c.id))"
                    . " ) > 0  ";
            }
            $sql .= ")";
        }

        if ($sameUserGroupAsMe) {
            //_error_log('getAllCategories getUserGroups');
            $users_groups = UserGroups::getUserGroups($sameUserGroupAsMe);

            $users_groups_id = array(0);
            foreach ($users_groups as $value) {
                $users_groups_id[] = $value['id'];
            }


            $sql .= " AND ("
                . "(SELECT count(*) FROM categories_has_users_groups chug WHERE c.id = chug.categories_id) = 0 OR "
                . "(SELECT count(*) FROM categories_has_users_groups chug2 WHERE c.id = chug2.categories_id AND users_groups_id IN (" . implode(',', $users_groups_id) . ")) >= 1 "
                . ")";
        }

        $sortWhitelist = ['id', 'name', 'clean_name', 'description', 'iconClass', 'nextVideoOrder', 'parentId', 'type', 'users_id', 'private', 'allow_download', 'order', 'suggested'];

        if (!empty($_POST['sort']) && is_array($_POST['sort'])) {
            foreach ($_POST['sort'] as $key => $value) {
                if (!in_array($key, $sortWhitelist)) {
                    unset($_POST['sort'][$key]);
                }
            }
        }

        $sql .= BootGrid::getSqlFromPost(['name'], "", " ORDER BY `order`, name ASC ");
        if (!empty($_GET['debug'])) {
            //echo $sql;exit;   
        }

        $timeLogName = TimeLogStart("getAllCategories");
        $cacheSuffix = md5($sql);
        $cacheHandler = new CategoryCacheHandler(0);
        $cacheObj = $cacheHandler->getCache($cacheSuffix, 36000);
        TimeLogEnd($timeLogName, __LINE__);
        $category = object_to_array($cacheObj);
        TimeLogEnd($timeLogName, __LINE__);
        //var_dump(!empty($cacheObj), !empty($category), debug_backtrace());
        if (empty($category)) {
            TimeLogEnd($timeLogName, __LINE__);
            $res = sqlDAL::readSql($sql);
            TimeLogEnd($timeLogName, __LINE__);
            $fullResult = sqlDAL::fetchAllAssoc($res);
            TimeLogEnd($timeLogName, __LINE__);
            sqlDAL::close($res);
            $category = [];
            if ($res) {
                foreach ($fullResult as $row) {

                    //_error_log("getAllCategories id={$row['id']} line=".__LINE__);
                    TimeLogEnd($timeLogName, __LINE__);
                    $totals = self::getTotalFromCategory($row['id']);
                    if ($onlyWithVideos && empty($totals['total'])) {
                        continue;
                    }
                    TimeLogEnd($timeLogName, __LINE__);
                    //_error_log("getAllCategories id={$row['id']} line=".__LINE__);
                    $fullTotals = self::getTotalFromCategory($row['id'], false, true, true);

                    $row['name'] = $row['name'];
                    $row['clean_name'] = $row['clean_name'];
                    $row['total'] = $totals['total'];
                    $row['fullTotal'] = $fullTotals['total'];
                    $row['fullTotal_videos'] = $fullTotals['videos'];
                    $row['fullTotal_lives'] = $fullTotals['lives'];
                    $row['fullTotal_livelinks'] = $fullTotals['livelinks'];
                    TimeLogEnd($timeLogName, __LINE__);
                    //_error_log("getAllCategories id={$row['id']} line=".__LINE__);
                    $row['owner'] = User::getNameIdentificationById(@$row['users_id']);
                    TimeLogEnd($timeLogName, __LINE__);
                    //_error_log("getAllCategories id={$row['id']} line=".__LINE__);
                    $row['canEdit'] = self::userCanEditCategory($row['id']);
                    TimeLogEnd($timeLogName, __LINE__);
                    //_error_log("getAllCategories id={$row['id']} line=".__LINE__);
                    $row['canAddVideo'] = self::userCanAddInCategory($row['id']);
                    TimeLogEnd($timeLogName, __LINE__);
                    //_error_log("getAllCategories id={$row['id']} line=".__LINE__);
                    $row['hierarchy'] = self::getHierarchyString($row['parentId']);
                    TimeLogEnd($timeLogName, __LINE__);
                    //_error_log("getAllCategories id={$row['id']} line=".__LINE__);
                    $row['hierarchyAndName'] = $row['hierarchy'] . __($row['name']);
                    //_error_log("getAllCategories id={$row['id']} line=".__LINE__);
                    $row['description_html'] = textToLink(htmlentities("{$row['description']}"));
                    TimeLogEnd($timeLogName, __LINE__);
                    $category[] = $row;
                    //break;
                }
                TimeLogEnd($timeLogName, __LINE__);
                $result = $cacheHandler->setCache($category);
                TimeLogEnd($timeLogName, __LINE__);
                //var_dump($category, $result);exit;

            } else {
                $category = false;
                //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        }
        //_error_log('getAllCategories return');
        return $category;
    }

    public static function getHierarchyArray($categories_id, $hierarchyArray = [])
    {
        if (empty($categories_id)) {
            return $hierarchyArray;
        }
        $sql = "SELECT * FROM categories WHERE id=? ";
        $res = sqlDAL::readSql($sql, "i", [$categories_id]);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($result) {
            $hierarchyArray[] = $result;
            if ($result['parentId'] != $categories_id) {
                return self::getHierarchyArray($result['parentId'], $hierarchyArray);
            }
        }
        return $hierarchyArray;
    }

    public static function getHierarchyString($categories_id)
    {
        if (empty($categories_id)) {
            return "/";
        }
        $array = array_reverse(self::getHierarchyArray($categories_id));
        //$array = (self::getHierarchyArray($categories_id));
        //var_dump($array);exit;
        if (empty($array)) {
            return "/";
        }
        $str = "/";
        foreach ($array as $value) {
            $str .= __(xss_esc_back($value['name'])) . "/";
        }
        return $str;
    }

    public static function userCanAddInCategory($categories_id, $users_id = 0)
    {
        if (empty($categories_id)) {
            return false;
        }
        if (isCommandLineInterface()) {
            return true;
        }
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        if (empty($users_id)) {
            return false;
        }
        $cat = new Category($categories_id);
        if (empty($cat->getPrivate()) || $users_id == $cat->getUsers_id()) {
            return true;
        }
        return false;
    }

    public static function userCanEditCategory($categories_id, $users_id = 0)
    {
        if (empty($categories_id)) {
            return false;
        }
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        if (empty($users_id)) {
            return false;
        }

        if (User::isAdmin()) {
            return true;
        }

        $cat = new Category($categories_id);
        if ($users_id == $cat->getUsers_id()) {
            return true;
        }
        return false;
    }

    public static function canCreateCategory()
    {
        global $advancedCustomUser;
        if (User::isAdmin()) {
            return true;
        }
        if ($advancedCustomUser && $advancedCustomUser->usersCanCreateNewCategories && User::canUpload()) {
            return true;
        }
        return false;
    }

    public static function getChildCategories($parentId, $filterCanAddVideoOnly = false)
    {
        global $global, $config;
        if ($config->currentVersionLowerThen('8.4')) {
            return false;
        }
        $sql = "SELECT * FROM categories WHERE parentId=? AND id!=? ";
        if ($filterCanAddVideoOnly && !User::isAdmin()) {
            if (is_int($filterCanAddVideoOnly)) {
                $users_id = $filterCanAddVideoOnly;
            } else {
                $users_id = User::getId();
            }

            if ($config->currentVersionGreaterThen('6.1')) {
                $sql .= " AND (private=0 OR users_id = '{$users_id}') ";
            }
        }

        unset($_POST['sort']['v.created']);
        unset($_POST['sort']['likes']);

        $sql .= BootGrid::getSqlFromPost(['name'], "", " ORDER BY `order`, name ASC ");
        //var_dump($sql, [$parentId, $parentId]);exit;
        $res = sqlDAL::readSql($sql, "ii", [$parentId, $parentId]);
        $fullResult = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $category = [];
        if ($res) {
            foreach ($fullResult as $row) {
                $totals = self::getTotalFromCategory($row['id']);
                $row['name'] = xss_esc_back($row['name']);
                $row['total'] = $totals['total'];
                $row['total_array'] = $totals;

                $category[] = $row;
            }
        } else {
            $category = false;
            //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $category;
    }

    public static function hasSubcategory($parentId)
    {
        global $global, $config;
        $sql = "SELECT count(id) as total FROM categories WHERE parentId=? AND id!=? ";

        $res = sqlDAL::readSql($sql, "ii", [$parentId, $parentId]);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            return intval($result['total']);
        }
        return 0;
    }

    public static function getChildCategoriesFromTitle($clean_title)
    {
        $row = self::getCategoryByName($clean_title);
        return self::getChildCategories($row['id']);
    }

    public static function getTotalFromCategory($categories_id, $showUnlisted = false, $getAllVideos = false, $renew = false)
    {
        global $global;
        $cacheSuffix = "getTotalFromCategory_{$categories_id}_" . intval($showUnlisted) . intval($getAllVideos);
        if (isset($global[$cacheSuffix])) {
            return $global[$cacheSuffix];
        }

        $timeLogName = TimeLogStart($cacheSuffix);
        $cacheHandler = new CategoryCacheHandler(0);
        TimeLogEnd($timeLogName, __LINE__);
        if (!$cacheHandler->hasCache($cacheSuffix, 0)) {
            $videos = self::getTotalVideosFromCategory($categories_id, $showUnlisted, $getAllVideos, $renew);
            TimeLogEnd($timeLogName, __LINE__);
            $lives = self::getTotalLivesFromCategory($categories_id, $showUnlisted, $renew);
            TimeLogEnd($timeLogName, __LINE__);
            $livelinkss = self::getTotalLiveLinksFromCategory($categories_id, $showUnlisted, $renew);
            TimeLogEnd($timeLogName, __LINE__);
            $total = $videos + $lives + $livelinkss;
            $result = ['videos' => $videos, 'lives' => $lives, 'livelinks' => $livelinkss, 'total' => $total];
            $cacheHandler->setCache($result);
            TimeLogEnd($timeLogName, __LINE__);
        } else {
            $result = $cacheHandler->getCache($cacheSuffix, 0);
            $result = object_to_array($result);
        }
        TimeLogEnd($timeLogName, __LINE__);
        $global[$cacheSuffix] = $result;
        return $result;
    }

    public static function getTotalFromChildCategory($categories_id, $showUnlisted = false, $getAllVideos = false, $renew = false)
    {
        $categories = self::getChildCategories($categories_id);
        $array = ['videos' => 0, 'lives' => 0, 'livelinks' => 0, 'total' => 0];
        foreach ($categories as $value) {
            $totals = self::getTotalFromCategory($categories_id, $showUnlisted, $getAllVideos, $renew);
            $array = [
                'videos' => $array['videos'] + $totals['videos'],
                'lives' => $array['lives'] + $totals['lives'],
                'livelinks' => $array['livelinks'] + $totals['livelinks'],
                'total' => $array['total'] + $totals['total'],
            ];
            $totals = self::getTotalFromChildCategory($value['id'], $showUnlisted, $getAllVideos, $renew);
            $array = [
                'videos' => $array['videos'] + $totals['videos'],
                'lives' => $array['lives'] + $totals['lives'],
                'livelinks' => $array['livelinks'] + $totals['livelinks'],
                'total' => $array['total'] + $totals['total'],
            ];
        }

        return $array;
    }

    public static function getLatestVideoFromCategory($categories_id, $showUnlisted = false, $getAllVideos = false)
    {
        global $global, $config;
        $sql = "SELECT * FROM videos v WHERE 1=1 AND (categories_id = ? OR categories_id IN (SELECT id from categories where parentId = ? ))";

        if (User::isLogged()) {
            $sql .= " AND (v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "') OR (v.status='u' AND v.users_id ='" . User::getId() . "'))";
        } else {
            $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "')";
        }
        if (!$getAllVideos) {
            $sql .= Video::getUserGroupsCanSeeSQL();
        }
        $sql .= "ORDER BY created DESC LIMIT 1";
        //var_dump($sql, $categories_id);
        $res = sqlDAL::readSql($sql, "ii", [$categories_id, $categories_id]);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return $result;
    }

    public static function getLatestLiveFromCategory($categories_id)
    {
        if (!AVideoPlugin::isEnabledByName("Live")) {
            return [];
        }
        global $global, $config;
        $sql = "SELECT * FROM live_transmitions lt LEFT JOIN live_transmitions_history lth ON lt.users_id = lth.users_id "
            . " WHERE 1=1 AND (categories_id = ? OR categories_id IN (SELECT id from categories where parentId = ?))";

        $sql .= "ORDER BY lth.created DESC LIMIT 1";
        //var_dump($sql, $categories_id);
        $res = sqlDAL::readSql($sql, "ii", [$categories_id, $categories_id]);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return $result;
    }

    public static function getLatestLiveLinksFromCategory($categories_id)
    {
        if (AVideoPlugin::isEnabledByName("LiveLinks")) {
            return [];
        }
        global $global, $config;
        $sql = "SELECT * FROM livelinks WHERE 1=1 AND (categories_id = ? OR categories_id IN (SELECT id from categories where parentId = ?))";

        $sql .= "ORDER BY created DESC LIMIT 1";
        //var_dump($sql, $categories_id);
        $res = sqlDAL::readSql($sql, "ii", [$categories_id, $categories_id]);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return $result;
    }

    public static function getTotalVideosFromCategory($categories_id, $showUnlisted = false, $getAllVideos = false, $renew = false)
    {
        global $global, $config;
        $cacheHandler = new CategoryCacheHandler($categories_id);

        $suffix = "totalVideos_" . intval($showUnlisted) . "_" . intval($getAllVideos);

        $timeLogName = TimeLogStart($suffix);
        $total = $cacheHandler->getCache($suffix);
        TimeLogEnd($timeLogName, __LINE__);

        if ($renew || (empty($total) && $total !== 0 && $total !== '0')) {
            $sql = "SELECT count(id) as total FROM videos v WHERE 1=1 AND categories_id = ? ";

            if (User::isLogged()) {
                $sql .= " AND (v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "') OR (v.status='u' AND v.users_id ='" . User::getId() . "'))";
            } else {
                $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "')";
            }
            if (!$getAllVideos) {
                $sql .= Video::getUserGroupsCanSeeSQL();
            }
            //echo $categories_id, $sql;exit;
            TimeLogEnd($timeLogName, __LINE__);
            $res = sqlDAL::readSql($sql, "i", [$categories_id]);
            TimeLogEnd($timeLogName, __LINE__);
            $fullResult = sqlDAL::fetchAllAssoc($res);
            TimeLogEnd($timeLogName, __LINE__);
            sqlDAL::close($res);
            $total = empty($fullResult[0]['total']) ? 0 : intval($fullResult[0]['total']);
            TimeLogEnd($timeLogName, __LINE__);
            $rows = self::getChildCategories($categories_id);
            TimeLogEnd($timeLogName, __LINE__);
            foreach ($rows as $value) {
                $total += self::getTotalVideosFromCategory($value['id'], $showUnlisted, $getAllVideos, $renew);
            }
            TimeLogEnd($timeLogName, __LINE__);
            $cacheHandler->setCache($total);
        }
        TimeLogEnd($timeLogName, __LINE__);
        return intval($total);
    }

    public static function getTotalLiveLinksFromCategory($categories_id, $showUnlisted = false, $renew = false)
    {
        global $global;

        if (!AVideoPlugin::isEnabledByName("LiveLinks")) {
            return 0;
        }

        $cacheHandler = new CategoryCacheHandler($categories_id);
        $suffix = "totalLiveLinks_" . intval($showUnlisted);
        $total = $cacheHandler->getCache($suffix);

        if ($renew || empty($total)) {
            $sql = "SELECT count(id) as total FROM LiveLinks v WHERE 1=1 AND categories_id = ? ";

            if (empty($showUnlisted)) {
                $sql .= " AND `type` = 'public' ";
            }

            //echo $categories_id, $sql;exit;
            $res = sqlDAL::readSql($sql, "i", [$categories_id]);
            $fullResult = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $total = empty($fullResult[0]['total']) ? 0 : intval($fullResult[0]['total']);
            $rows = self::getChildCategories($categories_id);
            foreach ($rows as $value) {
                $total += self::getTotalLivesFromCategory($value['id'], $showUnlisted, $renew);
            }

            $cacheHandler->setCache($total);
        }
        return intval($total);
    }

    public static function getTotalLivesFromCategory($categories_id, $showUnlisted = false, $renew = false)
    {
        if (!AVideoPlugin::isEnabledByName("Live")) {
            return 0;
        }

        global $advancedCustom;
        if (empty($advancedCustom)) {
            $advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");
        }
        if(empty($advancedCustom->categoryLiveCount)){
            return 0;
        }

        global $global;

        $cacheHandler = new CategoryCacheHandler($categories_id);
        $suffix = "totalLives_" . intval($showUnlisted);
        $total = $cacheHandler->getCache($suffix);

        if ($renew || (empty($total) && $total !== 0)) {
            $sql = "
            SELECT COUNT(DISTINCT v.id) AS total
            FROM live_transmitions v
            INNER JOIN live_transmitions_history h ON v.id = h.key
            WHERE v.categories_id = ? ";

            if (empty($showUnlisted)) {
                $sql .= " AND v.public = 1 ";
            }

            $res = sqlDAL::readSql($sql, "i", [$categories_id]);
            $fullResult = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $total = empty($fullResult[0]['total']) ? 0 : intval($fullResult[0]['total']);

            $rows = self::getChildCategories($categories_id);
            foreach ($rows as $value) {
                $total += self::getTotalLivesFromCategory($value['id'], $showUnlisted, $renew);
            }

            $cacheHandler->setCache(intval($total));
        }

        return intval($total);
    }


    public static function clearCacheCount($categories_id = 0)
    {
        // clear category count cache
        $cacheHandler = new CategoryCacheHandler($categories_id);
        $cacheHandler->deleteCache();
    }

    public function delete()
    {
        global $categoryDeleteMessage;
        $categoryDeleteMessage = '';
        if (!self::canCreateCategory()) {
            $categoryDeleteMessage = 'You cannot delete a category';
            return false;
        }

        if (!self::userCanEditCategory($this->id)) {
            $categoryDeleteMessage = 'This category does not belong to you';
            return false;
        }

        // cannot delete default category
        if ($this->id == 1) {
            $categoryDeleteMessage = 'You cannot delete the main category';
            return false;
        }

        global $global;
        if (!empty($this->id)) {
            _session_start();
            $_SESSION['user']['sessionCache']['getAllCategoriesClearCache'] = 1;
            $categories_id = self::getSiteCategoryDefaultID();
            if ($categories_id) {
                $sql = "UPDATE videos SET categories_id = ? WHERE categories_id = ?";
                sqlDAL::writeSql($sql, "ii", [$categories_id, $this->id]);
                $sql = "UPDATE live_transmitions SET categories_id = ? WHERE categories_id = ?";
                sqlDAL::writeSql($sql, "ii", [$categories_id, $this->id]);
            }
            $sql = "DELETE FROM categories WHERE id = ?";
        } else {
            $categoryDeleteMessage = 'Id is empty';
            return false;
        }
        self::deleteAssets($this->id);
        Category::deleteCategoryCache();
        return sqlDAL::writeSql($sql, "i", [$this->id]);
    }

    public static function getTotalCategories($filterCanAddVideoOnly = false, $onlyWithVideos = false, $onlySuggested = false)
    {
        global $global, $config;

        if ($config->currentVersionLowerThen('5.01')) {
            return false;
        }
        $sql = "SELECT id, parentId FROM categories c WHERE 1=1 ";
        if ($onlySuggested) {
            $sql .= "AND suggested = 1 ";
        }
        if ($filterCanAddVideoOnly && !User::isAdmin()) {
            if (is_int($filterCanAddVideoOnly)) {
                $users_id = $filterCanAddVideoOnly;
            } else {
                $users_id = User::getId();
            }

            if ($config->currentVersionGreaterThen('6.1')) {
                $sql .= " AND (private=0 OR users_id = '{$users_id}') ";
            }
        }
        if (!empty($_GET['parentsOnly'])) {
            $sql .= "AND parentId = 0 OR parentId = -1 ";
        }
        if ($onlyWithVideos) {
            $sql .= " AND ((SELECT count(*) FROM videos v where 1=1 ";
            if (isForKidsSet()) {
                $sql .= " AND v.made_for_kids = 1 ";
            }
            $sql .= " AND v.categories_id = c.id OR categories_id IN (SELECT id from categories where parentId = c.id AND id != c.id) ) > 0  ";
            if (AVideoPlugin::isEnabledByName("Live")) {
                $sql .= " OR "
                    . " ("
                    . " SELECT count(*) FROM live_transmitions lt where "
                    . " (lt.categories_id = c.id OR lt.categories_id IN (SELECT id from categories where parentId = c.id AND id != c.id))"
                    //. " AND lt.id = (select id FROM live_transmitions lt2 WHERE lt.users_id = lt2.users_id ORDER BY CREATED DESC LIMIT 1 )"
                    . " ) > 0  ";
            }
            if (AVideoPlugin::isEnabledByName("LiveLinks")) {
                $sql .= " OR "
                    . " ("
                    . " SELECT count(*) FROM LiveLinks ll where "
                    . " (ll.categories_id = c.id OR ll.categories_id IN (SELECT id from categories where parentId = c.id AND id != c.id))"
                    . " ) > 0  ";
            }
            $sql .= ")";
        }
        $sql .= BootGrid::getSqlSearchFromPost(['name']);
        //echo $sql;exit;
        $res = sqlDAL::readSql($sql);
        $numRows = sqlDal::num_rows($res);
        sqlDAL::close($res);
        return $numRows;
    }

    public function getIconClass()
    {
        if (empty($this->iconClass)) {
            return "fa fa-folder";
        }
        return $this->iconClass;
    }

    public function setIconClass($iconClass)
    {
        $this->iconClass = $iconClass;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getClean_name()
    {
        return $this->clean_name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getAllow_download()
    {
        return $this->allow_download;
    }

    public function setAllow_download($allow_download)
    {
        $this->allow_download = intval($allow_download);
    }

    public static function getCategoryDirPath($categories_id = "")
    {
        global $global;

        $dir = "videos/categories/assets/";
        if (!empty($categories_id)) {
            $dir .= $categories_id . "/";
        }

        $path = [];
        $path['dir'] = "{$global['systemRootPath']}{$dir}";
        make_path($path['dir']);
        $path['path'] = "{$global['systemRootPath']}{$dir}";
        $path['url'] = getCDN() . "{$dir}";
        return $path;
    }

    public static function isAssetsValids($categories_id)
    {
        $photo = Category::getCategoryPhotoPath($categories_id);
        $background = Category::getCategoryBackgroundPath($categories_id);
        //var_dump(filesize($background['path']), $background['path'], filesize($photo['path']), $photo['path'] );
        if (!@file_exists($photo['path']) || !@file_exists($background['path'])) {
            return false;
        }
        if (filesize($photo['path']) <= 190) { // transparent image
            return false;
        }
        if (filesize($background['path']) <= 980 || filesize($background['path']) == 4480) { // transparent image
            return false;
        }
        //return true;
        return !is_image_fully_transparent($photo['path']) && !is_image_fully_transparent($background['path']);
    }

    public static function getOGImagePaths($categories_id)
    {
        $name = "og_200X200.jpg";
        $dirPaths = self::getCategoryDirPath($categories_id);
        $path = [];
        $path['dir'] = $dirPaths['url'];
        $path['path'] = "{$dirPaths['path']}{$name}";
        $path['url'] = "{$dirPaths['url']}{$name}";
        if (file_exists($path['path'])) {
            $path['url+timestamp'] = "{$path['url']}?" . filectime($path['path']);
        } else {
            $path['url+timestamp'] = $path['url'];
        }
        return $path;
    }

    public static function deleteOGImage($categories_id)
    {
        $ogPaths = self::getOGImagePaths($categories_id);
        $destination = $ogPaths['path'];
        if (file_exists($destination)) {
            unlink($destination);
        }
    }

    public static function getOGImage($categories_id)
    {
        global $global;
        $isAssetsValids = self::isAssetsValids($categories_id);
        if ($isAssetsValids) {
            $ogPaths = self::getOGImagePaths($categories_id);
            $destination = $ogPaths['path'];
            if (!file_exists($destination)) {
                $photo = self::getCategoryPhotoPath($categories_id);
                $source = $photo['path'];
                convertImageToOG($source, $destination);
            }

            return $ogPaths['url+timestamp'];
        } else {
            return Configuration::getOGImage();
        }
    }

    public static function getCategoryPhotoPath($categories_id)
    {
        $path = self::getCategoryAssetPath("photo.png", $categories_id);
        return $path;
    }

    public static function getCategoryBackgroundPath($categories_id)
    {
        $path = self::getCategoryAssetPath("background.png", $categories_id);
        return $path;
    }

    private static function getCategoryAssetPath($name, $categories_id)
    {
        if (empty($categories_id)) {
            return false;
        }
        if (empty($name)) {
            return false;
        }

        $dirPaths = self::getCategoryDirPath($categories_id);

        global $global;

        $path = [];
        $path['dir'] = $dirPaths['url'];
        $path['path'] = "{$dirPaths['path']}{$name}";
        $path['url'] = "{$dirPaths['url']}{$name}";
        if (file_exists($path['path'])) {
            $path['url+timestamp'] = "{$path['url']}?" . filectime($path['path']);
        } else {
            $path['url+timestamp'] = $path['url'];
        }
        return $path;
    }

    public static function setUsergroups($categories_id, $usergroups_ids_array)
    {
        if (!is_array($usergroups_ids_array)) {
            $usergroups_ids_array = [$usergroups_ids_array];
        }
        Categories_has_users_groups::deleteAllFromCategory($categories_id);
        $return = [];
        foreach ($usergroups_ids_array as $users_groups_id) {
            $id = Categories_has_users_groups::saveUsergroup($categories_id, $users_groups_id);
            $return[] = $id;
        }
        return $return;
    }
}
