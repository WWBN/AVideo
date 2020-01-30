<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/category.php';

$_POST['rowCount'] = 2;
if(empty($_GET['current'])){
    $_POST['current'] = 1;
}else{
    $_POST['current'] = intval($_GET['current']);
}

$obj = AVideoPlugin::getObjectData("YouPHPFlix2");
$timeLog = __FILE__ . " - modeFlixCategory";

$uid = uniqid();
?>
<div class="categoriesContainerItem" id="<?php echo $uid; ?>">
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
    if ($obj->CategoriesAutoPlay) {
        $dataFlickirty->autoPlay = true;
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
    $_POST['rowCount'] = 2;
    $categories = Category::getAllCategories(false, true);
    if(empty($categories)){
        echo "</div>";
        return false;
    }
    $_POST['current']=1;
    $_POST['rowCount'] = $obj->maxVideos;
    $_POST['searchPhrase'] = $searchPhrase;
    $showAllVideos = false;
    if (!empty($_GET['catName'])) {
        $showAllVideos = true;
    }
    foreach ($categories as $value) {
        $obj = AVideoPlugin::getObjectData("YouPHPFlix2");
        $timeLog2 = __FILE__ . " - Category {$value['clean_name']}";
        TimeLogStart($timeLog2);
        $oldCatName = @$_GET['catName'];
        if (!empty($_GET['catName']) && $value['clean_name'] !== $_GET['catName']) {
            continue;
        } else {
            $_GET['catName'] = $value['clean_name'];
        }
        unset($_POST['sort']);
        $_POST['sort']['v.created'] = "DESC";
        $_POST['sort']['likes'] = "DESC";
        $videos = Video::getAllVideos("viewableNotUnlisted", false, true);
        TimeLogEnd($timeLog2, __LINE__);
        if (empty($videos)) {
            $_GET['catName'] = $oldCatName;
            continue;
        }
        ?>
        <div class="row topicRow">
            <span class="md-col-12">&nbsp;</span>
            <h2>
                <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_name']; ?>"><i class="<?php echo $value['iconClass']; ?>"></i> <?php echo $value['name']; ?></a>
            </h2>
            <!-- Categories -->
            <?php
            include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';

            if ($showAllVideos) {
                while (1) {
                    $_POST['current'] ++;
                    $videos = Video::getAllVideos("viewableNotUnlisted", false, true);
                    if (empty($videos)) {
                        break;
                    }
                    include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                }
            }
            ?>
        </div>
        <?php
        $_GET['catName'] = $oldCatName;
        TimeLogEnd($timeLog2, __LINE__);
    }
}
TimeLogEnd($timeLog, __LINE__);

?>
</div>
<p class="pagination">
    <a class="pagination__next" href="<?php echo $global['webSiteRootURL']; ?>plugin/YouPHPFlix2/view/modeFlixCategory.php?current=<?php echo count($categories)?$_POST['current'] + 1:$_POST['current']; ?>"></a>
</p>