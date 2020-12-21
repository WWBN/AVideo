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
        $this->name = xss_esc($name);
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

    /*
      function setType($type, $overwriteUserId = 0) {
      global $global;
      $internalId = $overwriteUserId;
      if (empty($internalId)) {
      $internalId = $this->id;
      }
      $exist = false;
      // require this cause of Video::autosetCategoryType - but should be moveable easy here..
      require_once dirname(__FILE__) . '/../objects/video.php';
      $sql = "SELECT * FROM `category_type_cache` WHERE categoryId = ?";
      $res = sqlDAL::readSql($sql, "i", array($internalId));
      $catTypeCache = sqlDAL::fetchAssoc($res);
      sqlDAL::close($res);
      if ($catTypeCache != false) {
      $exist = true;
      }

      if ($type == "3") {
      // auto-cat-type
      Video::autosetCategoryType($internalId);
      } else {
      if ($exist) {
      $sql = "UPDATE `category_type_cache` SET `type` = ?, `manualSet` = '1' WHERE `category_type_cache`.`categoryId` = ?;";
      sqlDAL::writeSql($sql, "si", array($type, $internalId));
      } else {
      $sql = "INSERT INTO `category_type_cache` (`categoryId`, `type`, `manualSet`) VALUES (?,?,'1')";
      sqlDAL::writeSql($sql, "is", array($internalId, $type));
      }
      }
      }
     * 
     */

    function setDescription($description) {
        $this->description = xss_esc($description);
    }

    function __construct($id, $name = '') {
        if (empty($id)) {
            // get the category data from category and pass
            $this->name = xss_esc($name);
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
                    . "`private` = ?, allow_download = ?, `order` = ?, modified = now() WHERE id = ?";
            $format = "sssiisiiiii";
            $values = array(xss_esc($this->name), xss_esc($this->clean_name), xss_esc($this->description), $this->nextVideoOrder, $this->parentId, $this->getIconClass(), $this->getUsers_id(), $this->getPrivate(), $this->getAllow_download(), $this->getOrder(), $this->id);
        } else {
            $sql = "INSERT INTO categories ( "
                    . "name,"
                    . "clean_name,"
                    . "description,"
                    . "nextVideoOrder,"
                    . "parentId,"
                    . "iconClass, "
                    . "users_id, "
                    . "`private`, allow_download, `order`, created, modified) VALUES (?, ?,?,?,?,?,?,?,?,?,now(), now())";
            $format = "sssiisiiii";
            $values = array(xss_esc($this->name), xss_esc($this->clean_name), xss_esc($this->description), $this->nextVideoOrder, $this->parentId, $this->getIconClass(), $this->getUsers_id(), $this->getPrivate(), $this->getAllow_download(), $this->getOrder());
        }

        $insert_row = sqlDAL::writeSql($sql, $format, $values);
        if ($insert_row) {
            $_SESSION['getAllCategoriesClearCache'] = 1;
            ObjectYPT::deleteALLCache();
            if (empty($this->id)) {
                $id = $global['mysqli']->insert_id;
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
            $_SESSION['getAllCategoriesClearCache'] = 1;
            $categories_id = self::getSiteCategoryDefaultID();
            if ($categories_id) {
                $sql = "UPDATE videos SET categories_id = ? WHERE categories_id = ?";
                sqlDAL::writeSql($sql, "ii", array($categories_id, $this->id));
            }
            $sql = "DELETE FROM categories WHERE id = ?";
        } else {
            return false;
        }
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

    static function getCategoryByName($name) {
        global $global;
        $sql = "SELECT * FROM categories WHERE clean_name = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", array($name));
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($result) {
            $result['name'] = xss_esc_back($result['name']);
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

    static function getAllCategories($filterCanAddVideoOnly = false, $onlyWithVideos = false) {
        global $global, $config;
        if ($config->currentVersionLowerThen('8.4')) {
            return false;
        }
        $sql = "SELECT * FROM categories c WHERE 1=1 ";
        if (!empty($_GET['parentsOnly'])) {
            $sql .= "AND parentId = 0 ";
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
            $sql .= " AND (SELECT count(*) FROM videos where categories_id = c.id OR categories_id IN (SELECT id from categories where parentId = c.id)) > 0  ";
        }
        if (isset($_POST['sort']['title'])) {
            unset($_POST['sort']['title']);
        }
        $sql .= BootGrid::getSqlFromPost(array('name'), "", " ORDER BY `order`, name ASC ");

        $cacheName = md5($sql);
        if (empty($_SESSION['getAllCategoriesClearCache'])) {
            $category = object_to_array(ObjectYPT::getCache($cacheName, 36000));
        } else {
            _session_start();
            unset($_SESSION['getAllCategoriesClearCache']);
        }
        if (empty($category)) {
            $res = sqlDAL::readSql($sql);
            $fullResult = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $category = array();
            if ($res) {
                foreach ($fullResult as $row) {
                    $row['name'] = xss_esc_back($row['name']);
                    $row['total'] = self::getTotalVideosFromCategory($row['id']);
                    $row['fullTotal'] = self::getTotalVideosFromCategory($row['id'], false, true, true);
                    $row['owner'] = User::getNameIdentificationById(@$row['users_id']);
                    $row['canEdit'] = self::userCanEditCategory($row['id']);
                    $row['canAddVideo'] = self::userCanAddInCategory($row['id']);
                    $row['hierarchy'] = self::getHierarchyString($row['parentId']);
                    $row['hierarchyAndName'] = $row['hierarchy'] . $row['name'];
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
                $row['name'] = xss_esc_back($row['name']);
                $row['total'] = self::getTotalVideosFromCategory($row['id']);
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

    static function getTotalVideosFromCategory($categories_id, $showUnlisted = false, $getAllVideos = false, $renew = false) {
        global $global, $config;
        if ($renew || empty($_SESSION['categoryTotal'][$categories_id][intval($showUnlisted)][intval($getAllVideos)])) {
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
            $_SESSION['categoryTotal'][$categories_id][intval($showUnlisted)][intval($getAllVideos)] = $total;
        }
        return $_SESSION['categoryTotal'][$categories_id][intval($showUnlisted)][intval($getAllVideos)];
    }

    static function clearCacheCount() {
        // clear category count cache
        _session_start();
        unset($_SESSION['categoryTotal']);
        $_SESSION['getAllCategoriesClearCache'] = 1;
        //session_write_close();
    }

    static function getTotalCategories($filterCanAddVideoOnly = false) {
        global $global, $config;

        if ($config->currentVersionLowerThen('5.01')) {
            return false;
        }
        $sql = "SELECT id, parentId FROM categories WHERE 1=1 ";
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
        $sql .= BootGrid::getSqlSearchFromPost(array('name'));
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
        $path['url'] = "{$global['webSiteRootURL']}{$dir}";
        return $path;
    }

    static function isAssetsValids($categories_id) {
        $photo = Category::getCategoryPhotoPath($categories_id);
        $background = Category::getCategoryBackgroundPath($categories_id);
        if(!file_exists($photo['path']) || !file_exists($background['path'])){
            return false;
        }
        return true;
    }

    static function getCategoryPhotoPath($categories_id) {
        return self::getCategoryAssetPath("photo.png", $categories_id);
    }

    static function getCategoryBackgroundPath($categories_id) {
        return self::getCategoryAssetPath("background.png", $categories_id);
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
