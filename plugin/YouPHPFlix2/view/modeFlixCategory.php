<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../../videos/configuration.php';
}
_session_write_close();
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/category.php';

$_REQUEST['rowCount'] = 2;
if (empty($_GET['current'])) {
    unsetCurrentPage();
} else {
    $_REQUEST['current'] = intval($_GET['current']);
}

$uid = '{serie_uid}';

$cacheName = "modeFlixCategory" . md5(json_encode($_GET)) . User::getId();
$cacheName .= isForKidsSet()?'forKids':'';

$cache = ObjectYPT::getCache($cacheName, 600);
if (!empty($cache)) {
    echo str_replace('{serie_uid}', uniqid(), $cache);
    return false;
}
_ob_start();
$obj = AVideoPlugin::getObjectData("YouPHPFlix2");
$timeLog = __FILE__ . " - modeFlixCategory";

$uid = uniqid();
$divUUID = $uid;

$ads2 = getAdsLeaderBoardTop2();
$videosCounter = 0;
?>
<div class="categoriesContainerItem" id="<?php echo $divUUID; ?>">
    <?php
    TimeLogStart($timeLog);
    if ($obj->Categories) {
        $dataFlickirty = new stdClass();
        $dataFlickirty->wrapAround = true;
        $dataFlickirty->pageDots = !empty($obj->pageDots);
        $dataFlickirty->lazyLoad = true;
        $dataFlickirty->fade = true;
        $dataFlickirty->setGallerySize = false;
        $dataFlickirty->cellAlign = 'left';
        $dataFlickirty->groupCells = true;
        if ($obj->CategoriesAutoPlay) {
            $dataFlickirty->autoPlay = 10000;
            $dataFlickirty->wrapAround = true;
        } else {
            $dataFlickirty->wrapAround = true;
        }
        $searchPhrase = "";
        if (!empty($_POST['searchPhrase'])) {
            $searchPhrase = $_POST['searchPhrase'];
            unset($_POST['searchPhrase']);
        }
        unset($_POST['sort']);

        if (!empty($_REQUEST['search'])) {
            $_REQUEST['rowCount'] = 1000;
        } else {
            $_REQUEST['rowCount'] = 2;
        }
        if (!empty($_REQUEST['catName'])) {
            $hideTitle = 1;
            $categories = array(Category::getCategoryByName($_REQUEST['catName']));
        } else {
            $categories = Category::getAllCategories(false, true, $obj->CategoriesShowOnlySuggested);
            unsetCurrentPage();
        }
        if (empty($categories)) {
            echo "</div>";
            return false;
        }
        $_REQUEST['rowCount'] = $obj->maxVideos;
        $_POST['searchPhrase'] = $searchPhrase;
        foreach ($categories as $value) {
            echo "<!-- {$value['clean_name']} --> ";
            $obj2 = AVideoPlugin::getObjectData("YouPHPFlix2");
            $timeLog2 = __FILE__ . " - Category {$value['clean_name']}";
            TimeLogStart($timeLog2);
            $oldCatName = @$_REQUEST['catName'];
            if (!empty($_REQUEST['catName']) && $value['clean_name'] !== $_REQUEST['catName']) {
                continue;
            } else {
                $_REQUEST['catName'] = $value['clean_name'];
            }
            unset($_POST['sort']);
            $_POST['sort']['v.created'] = "DESC";
            $_POST['sort']['likes'] = "DESC";

            TimeLogStart("modeFlixCategory.php getAllVideos");
            $videos = Video::getAllVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED, false, !$obj2->hidePrivateVideos);
            TimeLogEnd("modeFlixCategory.php getAllVideos", __LINE__);

            TimeLogEnd($timeLog2, __LINE__);
            if (empty($videos)) {
                $_REQUEST['catName'] = $oldCatName;
                continue;
            }
            if (!empty($ads2)) {
                ?>
                <div class="row text-center" style="padding: 10px;">
                    <?php echo $ads2; ?>
                </div>
                <?php
            }
            ?>
            <!-- mode flix category -->
            <div class="row topicRow">
                <span class="md-col-12">&nbsp;</span>
                <?php
                if (empty($hideTitle)) {
                    ?>
                    <h2>
                        <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_name']; ?>"><i class="<?php echo $value['iconClass']; ?>"></i> <?php echo $value['name']; ?></a>
                    </h2>
                    <?php
                }
                ?>
                <!-- Categories -->
                <?php
                TimeLogStart("modeFlixCategory.php include row");
                include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                TimeLogEnd("modeFlixCategory.php include row", __LINE__, 0.2);
                ?>
            </div>
            <?php
            $_REQUEST['catName'] = $oldCatName;
            TimeLogEnd($timeLog2, __LINE__);
        }
    }
    TimeLogEnd($timeLog, __LINE__);
    if (empty($videosCounter)) {
        include_once __DIR__.'/notFoundHTML.php';
        echo "</div>";
        return false;
    }
    ?>
    <script>
        startModeFlix('#<?php echo $divUUID; ?> .topicRow div');
    </script>
</div>
<p class="pagination">
    <?php
    $url = "{$global['webSiteRootURL']}plugin/YouPHPFlix2/view/modeFlixCategory.php";
    if (!empty($_REQUEST['catName'])) {
        $url = addQueryStringParameter($url, 'catName', $_REQUEST['catName']);
    }
    $search = getSearchVar();
    if (!empty($search)) {
        $url = addQueryStringParameter($url, 'search', $search);
    }
    $url = addQueryStringParameter($url, 'rrating', @$_GET['rrating']);
    $url = addQueryStringParameter($url, 'tags_id', intval(@$_GET['tags_id']));
    $url = addQueryStringParameter($url, 'current', count($categories) ? $_REQUEST['current'] + 1 : $_REQUEST['current']);
    ?>
    <a class="pagination__next" href="<?php echo $url; ?>"></a>
</p>
<?php
$cache = _ob_get_clean();

ObjectYPT::setCache($cacheName, $cache);

echo str_replace('{serie_uid}', uniqid(), $cache);
?>
