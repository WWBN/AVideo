<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once dirname(__FILE__) . '/../videos/configuration.php';
}

require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';

class Category {

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

    function getSuggested() {
        return empty($this->suggested) ? 0 : 1;
    }

    function setSuggested($suggested) {
        $this->suggested = empty($suggested) ? 0 : 1;
    }

    function getOrder() {
        return intval($this->order);
    }

    function setOrder($order) {
        $this->order = intval($order);
    }

    function getUsers_id() {
        if (empty($this->users_id)) {
            $this->users_id = User::getId();
        }
        return $this->users_id;
    }

    function getPrivate() {
        return $this->private;
    }

    function setUsers_id($users_id) {
        // only admin can change owner
        if (!empty($this->users_id) && !User::isAdmin()) {
            return false;
        }

        $this->users_id = intval($users_id);
    }

    function setPrivate($private) {
        $this->private = empty($private) ? 0 : 1;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setClean_name($clean_name) {
        $clean_name = preg_replace('/\W+/', '-', strtolower(cleanString($clean_name)));
        $this->clean_name = $clean_name;
    }

    function setNextVideoOrder($nextVideoOrder) {
        $this->nextVideoOrder = $nextVideoOrder;
    }

    function setParentId($parentId) {
        $this->parentId = $parentId;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function __construct($id, $name = '') {
        if (empty($id)) {
            // get the category data from category and pass
            $this->name = $name;
        } else {
            $this->id = $id;
            // get data from id
            $this->load($id);
        }
    }

    private function load($id) {
        $row = self::getCategory($id);
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    function loadSelfCategory() {
        $this->load($this->getId());
    }

    function save($allowOfflineUser = false) {
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
        if (!empty($exists) && $exists['id'] != $this->id) {
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
            $values = array($this->name, $this->clean_name, $this->description, intval($this->nextVideoOrder), $this->parentId, $this->getIconClass(), $this->getUsers_id(), $this->getSuggested(), $this->getPrivate(), $this->getAllow_download(), $this->getOrder(), $this->id);
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
            $values = array($this->name, $this->clean_name, $this->description, intval($this->nextVideoOrder), $this->parentId, $this->getIconClass(), $this->getUsers_id(), $this->getSuggested(), $this->getPrivate(), $this->getAllow_download(), $this->getOrder());
        }
        $insert_row = sqlDAL::writeSql($sql, $format, $values);
        if ($insert_row) {
            self::deleteOGImage($this->id);
            $_SESSION['user']['sessionCache']['getAllCategoriesClearCache'] = 1;
            ObjectYPT::deleteALLCache();
            if (empty($this->id)) {
                $id = $insert_row;
            } else {
                $id = $this->id;
            }
            return $id;
        } else {
            die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }

    static function fixCleanTitle($clean_title, $count, $id, $original_title = "") {
        global $global;

        if (empty($original_title)) {
            $original_title = $clean_title;
        }

        $sql = "SELECT * FROM categories WHERE clean_name = '{$clean_title}' ";
        if (!empty($id)) {
            $sql .= " AND id != {$id} ";
        }
        $sql .= " LIMIT 1";
        $res = sqlDAL::readSql($sql, "", array(), true);
        $cleanTitleExists = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($cleanTitleExists != false) {
            return self::fixCleanTitle($original_title . "-" . $count, $count + 1, $id, $original_title);
        }
        return $clean_title;
    }

    function delete() {
        if (!self::canCreateCategory()) {
            return false;
        }

        if (!self::userCanEditCategory($this->id)) {
            return false;
        }

        // cannot delete default category
        if ($this->id == 1) {
            return false;
        }

        global $global;
        if (!empty($this->id)) {
            $_SESSION['user']['sessionCache']['getAllCategoriesClearCache'] = 1;
            $categories_id = self::getSiteCategoryDefaultID();
            if ($categories_id) {
                $sql = "UPDATE videos SET categories_id = ? WHERE categories_id = ?";
                sqlDAL::writeSql($sql, "ii", array($categories_id, $this->id));
            }
            $sql = "DELETE FROM categories WHERE id = ?";
        } else {
            return false;
        }
        ObjectYPT::deleteALLCache();
        self::clearCacheCount();
        self::deleteAssets($this->id);
        return sqlDAL::writeSql($sql, "i", array($this->id));
    }

    static function deleteAssets($categories_id) {
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

    static function getCategory($id) {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM categories WHERE id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", array($id));
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($result) {
            $result['name'] = xss_esc_back($result['name']);
        }
        return ($res) ? $result : false;
    }

    static function getCategoryLink($id) {
        $cat = new Category($id);
        return self::getCategoryLinkFromName($cat->getClean_name());
    }

    static function getCategoryLinkFromName($clean_name) {
        global $global;
        return "{$global['webSiteRootURL']}cat/{$clean_name}";
    }
    
    function getLink(){
        return self::getCategoryLinkFromName($this->getClean_name());
    }

    static function getCategoryByName($name) {
        global $global;
        $sql = "SELECT * FROM categories WHERE clean_name = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", array($name));
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($result) {
            $result['name'] = xss_esc_back($result['name']);
            $result['description_html'] = textToLink(htmlentities($result['description']));
        }
        return ($res) ? $result : false;
    }

    static function getOrCreateCategoryByName($name) {
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

    static function getCategoryDefault() {
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

    static function getSiteCategoryDefaultID() {
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

    static function getAllCategories($filterCanAddVideoOnly = false, $onlyWithVideos = false, $onlySuggested = false) {
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
            $sql .= " AND ((SELECT count(*) FROM videos v where v.categories_id = c.id OR categories_id IN (SELECT id from categories where parentId = c.id)) > 0  ";
            if (AVideoPlugin::isEnabledByName("Live")) {
                $sql .= " OR "
                        . " ("
                        . " SELECT count(*) FROM live_transmitions lt where "
                        . " (lt.categories_id = c.id OR lt.categories_id IN (SELECT id from categories where parentId = c.id))"
                        //. " AND lt.id = (select id FROM live_transmitions lt2 WHERE lt.users_id = lt2.users_id ORDER BY CREATED DESC LIMIT 1 )"
                        . " ) > 0  ";
            }
            if (AVideoPlugin::isEnabledByName("LiveLinks")) {
                $sql .= " OR "
                        . " ("
                        . " SELECT count(*) FROM LiveLinks ll where "
                        . " (ll.categories_id = c.id OR ll.categories_id IN (SELECT id from categories where parentId = c.id))"
                        . " ) > 0  ";
            }
            $sql .= ")";
        }

        $sortWhitelist = array('id', 'name', 'clean_name', 'description', 'iconClass', 'nextVideoOrder', 'parentId', 'type', 'users_id', 'private', 'allow_download', 'order', 'suggested');

        if (!empty($_POST['sort']) && is_array($_POST['sort'])) {
            foreach ($_POST['sort'] as $key => $value) {
                if (!in_array($key, $sortWhitelist)) {
                    unset($_POST['sort'][$key]);
                }
            }
        }
        $sql .= BootGrid::getSqlFromPost(array('name'), "", " ORDER BY `order`, name ASC ");
        //echo $sql;exit;
        $cacheName = md5($sql);
        if (empty($_SESSION['user']['sessionCache']['getAllCategoriesClearCache'])) {
            $category = object_to_array(ObjectYPT::getCache($cacheName, 36000));
        } else {
            _session_start();
            unset($_SESSION['user']['sessionCache']['getAllCategoriesClearCache']);
        }
        if (empty($category)) {
            $res = sqlDAL::readSql($sql);
            $fullResult = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $category = array();
            if ($res) {
                foreach ($fullResult as $row) {

                    $totals = self::getTotalFromCategory($row['id']);
                    $fullTotals = self::getTotalFromCategory($row['id'], false, true, true);

                    $row['name'] = $row['name'];
                    $row['total'] = $totals['total'];
                    $row['fullTotal'] = $fullTotals['total'];
                    $row['fullTotal_videos'] = $fullTotals['videos'];
                    $row['fullTotal_lives'] = $fullTotals['lives'];
                    $row['fullTotal_livelinks'] = $fullTotals['livelinks'];
                    $row['owner'] = User::getNameIdentificationById(@$row['users_id']);
                    $row['canEdit'] = self::userCanEditCategory($row['id']);
                    $row['canAddVideo'] = self::userCanAddInCategory($row['id']);
                    $row['hierarchy'] = self::getHierarchyString($row['parentId']);
                    $row['hierarchyAndName'] = $row['hierarchy'] . $row['name'];
                    $row['description_html'] = textToLink(htmlentities($row['description']));
                    $category[] = $row;
                }
                //$category = $res->fetch_all(MYSQLI_ASSOC);
                ObjectYPT::setCache($cacheName, $category);
            } else {
                $category = false;
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
        }
        return $category;
    }

    static function getHierarchyArray($categories_id, $hierarchyArray = array()) {
        if (empty($categories_id)) {
            return $hierarchyArray;
        }
        $sql = "SELECT * FROM categories WHERE id=? ";
        $res = sqlDAL::readSql($sql, "i", array($categories_id));
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($result) {
            $hierarchyArray[] = $result;
            return self::getHierarchyArray($result['parentId'], $hierarchyArray);
        }
        return $hierarchyArray;
    }

    static function getHierarchyString($categories_id) {
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
            $str .= xss_esc_back($value['name']) . "/";
        }
        return $str;
    }

    static function userCanAddInCategory($categories_id, $users_id = 0) {
        if (empty($categories_id)) {
            return false;
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

    static function userCanEditCategory($categories_id, $users_id = 0) {
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

    static function canCreateCategory() {
        global $advancedCustomUser;
        if (User::isAdmin()) {
            return true;
        }
        if ($advancedCustomUser && $advancedCustomUser->usersCanCreateNewCategories && User::canUpload()) {
            return true;
        }
        return false;
    }

    static function getChildCategories($parentId, $filterCanAddVideoOnly = false) {
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
        $sql .= BootGrid::getSqlFromPost(array('name'), "", " ORDER BY `order`, name ASC ");
        $res = sqlDAL::readSql($sql, "ii", array($parentId, $parentId));
        $fullResult = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $category = array();
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
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $category;
    }

    static function getChildCategoriesFromTitle($clean_title) {
        $row = self::getCategoryByName($clean_title);
        return self::getChildCategories($row['id']);
    }

    static function getTotalFromCategory($categories_id, $showUnlisted = false, $getAllVideos = false, $renew = false) {
        $videos = self::getTotalVideosFromCategory($categories_id, $showUnlisted, $getAllVideos, $renew);
        $lives = self::getTotalLivesFromCategory($categories_id, $showUnlisted, $renew);
        $livelinkss = self::getTotalLiveLinksFromCategory($categories_id, $showUnlisted, $renew);
        $total = $videos + $lives + $livelinkss;
        return array('videos' => $videos, 'lives' => $lives, 'livelinks' => $livelinkss, 'total' => $total);
    }

    static function getTotalFromChildCategory($categories_id, $showUnlisted = false, $getAllVideos = false, $renew = false) {

        $categories = self::getChildCategories($categories_id);
        $array = array('videos' => 0, 'lives' => 0, 'livelinks' => 0, 'total' => 0);
        foreach ($categories as $value) {
            $totals = self::getTotalFromCategory($categories_id, $showUnlisted, $getAllVideos, $renew);
            $array = array(
                'videos' => $array['videos'] + $totals['videos'],
                'lives' => $array['lives'] + $totals['lives'],
                'livelinks' => $array['livelinks'] + $totals['livelinks'],
                'total' => $array['total'] + $totals['total']);
            $totals = self::getTotalFromChildCategory($value['id'], $showUnlisted, $getAllVideos, $renew);
            $array = array(
                'videos' => $array['videos'] + $totals['videos'],
                'lives' => $array['lives'] + $totals['lives'],
                'livelinks' => $array['livelinks'] + $totals['livelinks'],
                'total' => $array['total'] + $totals['total']);
        }

        return $array;
    }

    static function getTotalVideosFromCategory($categories_id, $showUnlisted = false, $getAllVideos = false, $renew = false) {
        global $global, $config;
        if ($renew || empty($_SESSION['user']['sessionCache']['categoryTotal'][$categories_id][intval($showUnlisted)][intval($getAllVideos)]['videos'])) {
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
            $res = sqlDAL::readSql($sql, "i", array($categories_id));
            $fullResult = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $total = empty($fullResult[0]['total']) ? 0 : intval($fullResult[0]['total']);
            $rows = self::getChildCategories($categories_id);
            foreach ($rows as $value) {
                $total += self::getTotalVideosFromCategory($value['id'], $showUnlisted, $getAllVideos, $renew);
            }
            _session_start();
            $_SESSION['user']['sessionCache']['categoryTotal'][$categories_id][intval($showUnlisted)][intval($getAllVideos)]['videos'] = $total;
        }
        return $_SESSION['user']['sessionCache']['categoryTotal'][$categories_id][intval($showUnlisted)][intval($getAllVideos)]['videos'];
    }

    static function getLatestVideoFromCategory($categories_id, $showUnlisted = false, $getAllVideos = false) {
        global $global, $config;
        $sql = "SELECT * FROM videos v WHERE 1=1 AND (categories_id = ? OR categories_id IN (SELECT id from categories where parentId = ?))";

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
        $res = sqlDAL::readSql($sql, "ii", array($categories_id, $categories_id));
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return $result;
    }

    static function getLatestLiveFromCategory($categories_id) {
        if (!AVideoPlugin::isEnabledByName("Live")) {
            return array();
        }
        global $global, $config;
        $sql = "SELECT * FROM live_transmitions lt LEFT JOIN live_transmitions_history lth ON lt.users_id = lth.users_id "
                . " WHERE 1=1 AND (categories_id = ? OR categories_id IN (SELECT id from categories where parentId = ?))";

        $sql .= "ORDER BY lth.created DESC LIMIT 1";
        //var_dump($sql, $categories_id);
        $res = sqlDAL::readSql($sql, "ii", array($categories_id, $categories_id));
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return $result;
    }

    static function getLatestLiveLinksFromCategory($categories_id) {
        if (AVideoPlugin::isEnabledByName("LiveLinks")) {
            return array();
        }
        global $global, $config;
        $sql = "SELECT * FROM livelinks WHERE 1=1 AND (categories_id = ? OR categories_id IN (SELECT id from categories where parentId = ?))";

        $sql .= "ORDER BY created DESC LIMIT 1";
        //var_dump($sql, $categories_id);
        $res = sqlDAL::readSql($sql, "ii", array($categories_id, $categories_id));
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return $result;
    }

    static function getTotalLiveLinksFromCategory($categories_id, $showUnlisted = false, $renew = false) {
        global $global;

        if (!AVideoPlugin::isEnabledByName("LiveLinks")) {
            return 0;
        }

        if ($renew || empty($_SESSION['user']['sessionCache']['categoryTotal'][$categories_id][intval($showUnlisted)][0]['livelinks'])) {
            $sql = "SELECT count(id) as total FROM LiveLinks v WHERE 1=1 AND categories_id = ? ";

            if (empty($showUnlisted)) {
                $sql .= " AND `type` = 'public' ";
            }

            //echo $categories_id, $sql;exit;
            $res = sqlDAL::readSql($sql, "i", array($categories_id));
            $fullResult = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $total = empty($fullResult[0]['total']) ? 0 : intval($fullResult[0]['total']);
            $rows = self::getChildCategories($categories_id);
            foreach ($rows as $value) {
                $total += self::getTotalLivesFromCategory($value['id'], $showUnlisted, $renew);
            }
            _session_start();
            $_SESSION['user']['sessionCache']['categoryTotal'][$categories_id][intval($showUnlisted)][0]['livelinks'] = $total;
        }
        return $_SESSION['user']['sessionCache']['categoryTotal'][$categories_id][intval($showUnlisted)][0]['livelinks'];
    }

    static function getTotalLivesFromCategory($categories_id, $showUnlisted = false, $renew = false) {


        if (!AVideoPlugin::isEnabledByName("Live")) {
            return 0;
        }

        global $global;
        if ($renew || empty($_SESSION['user']['sessionCache']['categoryTotal'][$categories_id][intval($showUnlisted)][0]['live'])) {
            $sql = "SELECT count(id) as total FROM live_transmitions v WHERE 1=1 AND categories_id = ? ";

            if (empty($showUnlisted)) {
                $sql .= " AND public = 1 ";
            }

            //echo $categories_id, $sql;exit;
            $res = sqlDAL::readSql($sql, "i", array($categories_id));
            $fullResult = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $total = empty($fullResult[0]['total']) ? 0 : intval($fullResult[0]['total']);
            $rows = self::getChildCategories($categories_id);
            foreach ($rows as $value) {
                $total += self::getTotalLivesFromCategory($value['id'], $showUnlisted, $renew);
            }
            _session_start();
            $_SESSION['user']['sessionCache']['categoryTotal'][$categories_id][intval($showUnlisted)][0]['live'] = $total;
        }
        return $_SESSION['user']['sessionCache']['categoryTotal'][$categories_id][intval($showUnlisted)][0]['live'];
    }

    static function clearCacheCount($categories_id = 0) {
        // clear category count cache
        _session_start();
        if (empty($categories_id)) {
            unset($_SESSION['user']['sessionCache']['categoryTotal']);
            $_SESSION['user']['sessionCache']['getAllCategoriesClearCache'] = 1;
        } else {
            unset($_SESSION['user']['sessionCache']['categoryTotal'][$categories_id]);
        }
        //session_write_close();
    }

    static function getTotalCategories($filterCanAddVideoOnly = false, $onlyWithVideos = false, $onlySuggested = false) {
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
            $sql .= " AND ((SELECT count(*) FROM videos v where v.categories_id = c.id OR categories_id IN (SELECT id from categories where parentId = c.id)) > 0  ";
            if (AVideoPlugin::isEnabledByName("Live")) {
                $sql .= " OR "
                        . " ("
                        . " SELECT count(*) FROM live_transmitions lt where "
                        . " (lt.categories_id = c.id OR lt.categories_id IN (SELECT id from categories where parentId = c.id))"
                        //. " AND lt.id = (select id FROM live_transmitions lt2 WHERE lt.users_id = lt2.users_id ORDER BY CREATED DESC LIMIT 1 )"
                        . " ) > 0  ";
            }
            if (AVideoPlugin::isEnabledByName("LiveLinks")) {
                $sql .= " OR "
                        . " ("
                        . " SELECT count(*) FROM LiveLinks ll where "
                        . " (ll.categories_id = c.id OR ll.categories_id IN (SELECT id from categories where parentId = c.id))"
                        . " ) > 0  ";
            }
            $sql .= ")";
        }
        $sql .= BootGrid::getSqlSearchFromPost(array('name'));
        //echo $sql;exit;
        $res = sqlDAL::readSql($sql);
        $numRows = sqlDal::num_rows($res);
        sqlDAL::close($res);
        return $numRows;
    }

    function getIconClass() {
        if (empty($this->iconClass)) {
            return "fa fa-folder";
        }
        return $this->iconClass;
    }

    function setIconClass($iconClass) {
        $this->iconClass = $iconClass;
    }

    function getName() {
        return $this->name;
    }

    function getClean_name() {
        return $this->clean_name;
    }

    function getDescription() {
        return $this->description;
    }

    function getAllow_download() {
        return $this->allow_download;
    }

    function setAllow_download($allow_download) {
        $this->allow_download = intval($allow_download);
    }

    static function getCategoryDirPath($categories_id = "") {
        global $global;

        $dir = "videos/categories/assets/";
        if (!empty($categories_id)) {
            $dir .= $categories_id . "/";
        }

        $path = array();
        $path['dir'] = "{$global['systemRootPath']}{$dir}";
        make_path($path['dir']);
        $path['path'] = "{$global['systemRootPath']}{$dir}";
        $path['url'] = getCDN() . "{$dir}";
        return $path;
    }

    static function isAssetsValids($categories_id) {
        $photo = Category::getCategoryPhotoPath($categories_id);
        $background = Category::getCategoryBackgroundPath($categories_id);
        //var_dump(filesize($background['path']), $background['path'], filesize($photo['path']), $photo['path'] );
        if (!file_exists($photo['path']) || !file_exists($background['path'])) {
            return false;
        }
        if (filesize($photo['path']) <= 190) { // transparent image
            return false;
        }
        if (filesize($background['path']) <= 980 || filesize($background['path']) == 4480) { // transparent image
            return false;
        }
        return true;
    }

    public static function getOGImagePaths($categories_id) {
        $name = "og_200X200.jpg";
        $dirPaths = self::getCategoryDirPath($categories_id);
        $path = array();
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

    public static function deleteOGImage($categories_id) {
        $ogPaths = self::getOGImagePaths($categories_id);
        $destination = $ogPaths['path'];
        if (file_exists($destination)) {
            unlink($destination);
        }
    }

    public static function getOGImage($categories_id) {
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

    static function getCategoryPhotoPath($categories_id) {
        $path = self::getCategoryAssetPath("photo.png", $categories_id);
        return $path;
    }

    static function getCategoryBackgroundPath($categories_id) {
        $path = self::getCategoryAssetPath("background.png", $categories_id);
        return $path;
    }

    private static function getCategoryAssetPath($name, $categories_id) {
        if (empty($categories_id)) {
            return false;
        }
        if (empty($name)) {
            return false;
        }

        $dirPaths = self::getCategoryDirPath($categories_id);

        global $global;

        $path = array();
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

}
