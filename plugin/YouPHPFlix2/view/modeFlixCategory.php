<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../../videos/configuration.php';
}
session_write_close();
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/category.php';

$_REQUEST['rowCount'] = 2;
if(empty($_GET['current'])){
    $_REQUEST['current'] = 1;
}else{
    $_REQUEST['current'] = intval($_GET['current']);
}

$obj = AVideoPlugin::getObjectData("YouPHPFlix2");
$timeLog = __FILE__ . " - modeFlixCategory";

$uid = uniqid();
?>
<div class="categoriesContainerItem" id="<?php echo $uid; ?>">
<?php
$ads2 = getAdsLeaderBoardTop2();
if (!empty($ads2)) {
    ?>
    <div class="row text-center" style="padding: 10px;">
        <?php echo $ads2; ?>
    </div>
    <?php
}
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
    $_REQUEST['rowCount'] = 2;    
    $categories = Category::getAllCategories(false, true);
    if(empty($categories)){
        echo "</div>";
        return false;
    }
    $_REQUEST['current']=1;
    $_REQUEST['rowCount'] = $obj->maxVideos;
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
        
        TimeLogStart("modeFlixCategory.php getAllVideos");
        $videos = Video::getAllVideos("viewableNotUnlisted", false, true);        
        TimeLogEnd("modeFlixCategory.php getAllVideos", __LINE__);
        
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
            
            TimeLogStart("modeFlixCategory.php include row");
            include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';     
            TimeLogEnd("modeFlixCategory.php include row", __LINE__, 0.2);

            if ($showAllVideos) {
                TimeLogStart("modeFlixCategory.php showAllVideos");
                while (1) {
                    $_REQUEST['current'] ++;
                    $videos = Video::getAllVideos("viewableNotUnlisted", false, true);
                    if (empty($videos)) {
                        break;
                    }
                    include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                } 
                TimeLogEnd("modeFlixCategory.php showAllVideos", __LINE__, 0.2);
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
    <a class="pagination__next" href="<?php echo $global['webSiteRootURL']; ?>plugin/YouPHPFlix2/view/modeFlixCategory.php?current=<?php echo count($categories)?$_REQUEST['current'] + 1:$_REQUEST['current']; ?>&rrating=<?php echo @$_GET['rrating']; ?>"></a>
</p>