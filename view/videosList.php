<?php
global $global, $config;
$firstTimeLoading = 1;
if (!isset($global['systemRootPath'])) {
    $firstTimeLoading = 0;
    require_once '../videos/configuration.php';
}

require_once $global['systemRootPath'].'objects/functionInfiniteScroll.php';
$videos_id = getVideos_id();

$sortOptions = [
    ['key' => 'title', 'order' => 'asc', 'sortBy' => 'titleAZ', 'label' => __("Title (A-Z)"), 'data-icon' => '<i class="fas fa-sort-alpha-down"></i>'],
    ['key' => 'title', 'order' => 'desc', 'sortBy' => 'titleZA', 'label' => __("Title (Z-A)"), 'data-icon' => '<i class="fas fa-sort-alpha-down-alt"></i>'],
    ['key' => 'created', 'order' => 'desc', 'sortBy' => 'newest', 'label' => __("Date added (newest)"), 'data-icon' => '<i class="fas fa-sort-numeric-down"></i>'],
    ['key' => 'created', 'order' => 'asc', 'sortBy' => 'oldest', 'label' => __("Date added (oldest)"), 'data-icon' => '<i class="fas fa-sort-numeric-down"></i>'],
    ['key' => 'likes', 'order' => 'desc', 'sortBy' => 'popular', 'label' => __("Most popular"), 'data-icon' => '<i class="far fa-thumbs-up"></i>'],
    ['key' => 'suggested', 'order' => 'desc', 'sortBy' => 'suggested', 'label' => __("Suggested"), 'data-icon' => '<i class="fas fa-star"></i>'],
    ['key' => 'trending', 'order' => 'desc', 'sortBy' => 'trending', 'label' => __("Trending"), 'data-icon' => '<i class="fas fa-fire"></i>'],
];

if (empty($advancedCustom->doNotDisplayViews)) {
    $sortOptions[] = ['key' => 'views_count', 'order' => 'desc', 'sortBy' => 'views_count', 'label' => __("Most watched"), 'data-icon' => '<i class="fas fa-eye"></i>'];
}

$sortBy = $advancedCustom->sortVideoListByDefault->value;
if (!empty($_REQUEST['sortBy'])) {
    $sortBy = $_REQUEST['sortBy'];
} else if (!empty($_SESSION['sortBy'])) {
    $sortBy = $_SESSION['sortBy'];
}
$sortBy = strtolower($sortBy);

_session_start();
if (empty($_REQUEST['rowCount']) && empty($_SESSION['rowCount'])) {
    $_SESSION['rowCount'] = getRowCount();
} else if (!empty($_REQUEST['rowCount'])) {
    $_SESSION['rowCount'] = $_REQUEST['rowCount'];
}

$jsonRowCountArray = _json_decode($advancedCustom->videosListRowCount);

if(empty($jsonRowCountArray) || !is_array($jsonRowCountArray)){
    $jsonRowCountArray = [10,20,30,40,50];
}

if (!in_array($_SESSION['rowCount'], $jsonRowCountArray)) {
    $_SESSION['rowCount'] = $jsonRowCountArray[0];
}

$_REQUEST['rowCount'] = $_SESSION['rowCount'];
$_SESSION['sortBy'] = $sortBy;

$_POST['sort'] = [];
foreach ($sortOptions as $value) {
    //var_dump($sortBy, strtolower($value['sortBy']), $sortBy === strtolower($value['sortBy']));echo '<hr>';
    if ($sortBy === strtolower($value['sortBy'])) {
        $_POST['sort'][$value['key']] = $value['order'];
        break;
    }
}

$searchForVideosNow = preg_match('/videosList.php$/', $_SERVER['PHP_SELF']);

//var_dump($_POST['sort']);
if ($searchForVideosNow) {
    $videos = Video::getAllVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED);
    if (empty($videos)) {
        //echo '<div id="videosList"></div>';
        exit;
    }
    $total = Video::getTotalVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED);
    $totalPages = ceil($total / getRowCount());
    if (empty($totalPages)) {
        $totalPages = 1;
    }
}
if (!empty($_REQUEST['channelName']) && empty($advancedCustomUser->hideRemoveChannelFromModeYoutube)) {
    $user = User::getChannelOwner($_REQUEST['channelName']);
    //var_dump($user);exit;
    ?>
    <div class="col-md-12" style="padding: 15px; margin: 5px 0; background-image: url(<?php echo $global['webSiteRootURL'], User::getBackgroundURLFromUserID($user['id']); ?>); background-size: cover;"  >
        <img src="<?php echo User::getPhoto($user['id']); ?>" class="img img-responsive img-circle" style="max-width: 60px;" alt="User Photo"/>
        <div style="position: absolute; right: 5px; top: 5px;">
            <button class="btn btn-default btn-xs btn-sm" onclick="loadPage(<?php echo @$_GET['page']; ?>, true);"><?php echo User::getNameIdentificationById($user['id']); ?> <i class="fa fa-times"></i></button>
        </div>
    </div>
    <?php
}

$objGallery = AVideoPlugin::getObjectData("Gallery");
?>
<div class="col-md-8 col-sm-12 " style="position: relative; z-index: 10;" >
    <?php
    $optionsArray = [];
    $selected = false;
    foreach ($sortOptions as $value) {
        $optionsArray[] = [htmlentities("{$value['data-icon']} {$value['label']}"), $value['sortBy'], 'order="' . $value['order'] . '"  key="' . $value['key'] . '"'];
        //var_dump($sortBy, strtolower($value['sortBy']), $sortBy === strtolower($value['sortBy']));echo '<hr>';
        if ($sortBy === strtolower($value['sortBy'])) {
            $selected = $value['sortBy'];
        }
    }
    //var_dump($sortBy, $selected);
    echo Layout::getSelectSearchableHTML($optionsArray, 'sortBy', $selected);
    ?>
</div>
<div class="col-md-4 col-sm-12" style="position: relative; z-index: 2;">
    <select class="form-control" id="rowCount">
        <?php
        foreach ($jsonRowCountArray as $item) {
            if ($item == -1) {
                ?>
                <option <?php echo (!empty($_REQUEST['rowCount']) && $_REQUEST['rowCount'] == $item) ? "selected='selected'" : "" ?>><?php echo __("All"); ?></option>
                <?php
            } else {
                ?>
                <option <?php echo (!empty($_REQUEST['rowCount']) && $_REQUEST['rowCount'] == $item) ? "selected='selected'" : "" ?>><?php echo $item; ?></option>
                <?php
            }
        }
        ?>
    </select>
</div>
<div id="videosListItems">
    <?php
    $link = "{$global['webSiteRootURL']}view/videosList.php";
    $link = addQueryStringParameter($link, 'videos_id', $videos_id);
    $link = addQueryStringParameter($link, 'channelName', @$_REQUEST['channelName']);
    $link = addQueryStringParameter($link, 'sortBy', $sortBy);
    if ($searchForVideosNow) {
        //var_dump($_SERVER['PHP_SELF']);
        //var_dump($sortBy, $_POST['sort'], $_SESSION['sort']);//exit;
        $getVideosListItem = '';
        foreach ($videos as $key => $value) {
            if (!empty($videos_id) && $videos_id == $value['id']) {
                continue; // skip video
            }
            $getVideosListItem .= Video::getVideosListItem($value['id']);
        }
        echo $getVideosListItem;
        //var_dump(getRowCount(), $totalPages, getCurrentPage(), $link);
        echo getPagination($totalPages, $link, 5);
    } else {
        for($i=0;$i<1;$i++){
        ?>
        <div class="loadingVideosList col-lg-12 col-sm-12 col-xs-12 bottom-border videoListItem videoList-PHP ">
            <div class="col-lg-5 col-sm-5 col-xs-5 nopadding thumbsImage videoLink h6">
                <div class="galleryVideo loading-background">
                    <img src="<?php echo ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_URL); ?>" alt="Loading"  class="thumbsJPG img-responsive text-center" height="130" />
                </div>
            </div>
            <div class="col-lg-7 col-sm-7 col-xs-7 videosDetails">
                <div class="row" ><strong class="title">...</strong></div>
                <div class="details row">
                    <div class="text-muted pull-right" style="display:flex;">
                        <div class="label label-default alreadyTooltip" data-toggle="tooltip" title="" style="" data-original-title="Watching Now">
                            <i class="fa fa-eye"></i>
                            <b class=""><i class="fas fa-circle-notch fa-spin"></i></b>
                        </div>
                        <div class="label label-default alreadyTooltip" data-toggle="tooltip" title="" data-original-title="Total Views">
                            <i class="fa fa-user"></i>
                            <b class=""><i class="fas fa-circle-notch fa-spin"></i></b>
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top: 5px;">
                    <div class="videoCreatorSmall">
                        <img src="<?php echo ImagesPlaceHolders::getUserIcon(ImagesPlaceHolders::$RETURN_URL); ?>" alt="Loading" class="img img-responsive img-circle zoom" />
                            ...
                    </div>
                </div>
            </div>
        </div>
        <?php
        }
    }
    ?>
</div>
<script>
    $(function () {
        loadVideosListPageTransformLinks();
        $('#sortBy, #rowCount').change(function () {
            loadVideosListPage(1);
        });
<?php
if (!$searchForVideosNow) {
    echo 'videosListDidNotSearchForVideos();';
}
?>
    });

    var loadVideosListPagerowCount = 'loadVideosListPagerowCount<?php User::getId(); ?>';
    var loadVideosListPagesortBy = 'loadVideosListPagesortBy<?php User::getId(); ?>';
    var loadVideosListPageTimeout;
    var loadVideosListPageIsLoading = false;

    function loadVideosListPage(page) {
        clearTimeout(loadVideosListPageTimeout);
        if (typeof modal === 'undefined') {
            setTimeout(function () {
                loadVideosListPageTimeout = loadVideosListPage(page);
            }, 500);
            return false;
        }
        if(loadVideosListPageIsLoading){
            return false;
        }
        loadVideosListPageIsLoading = true;
        var url = '<?php echo $link; ?>';

        var rowCount = $('#rowCount').val();
        var sortBy = $('#sortBy').val();

        Cookies.set(loadVideosListPagerowCount, rowCount, {
            path: '/',
            expires: 365
        });
        Cookies.set(loadVideosListPagesortBy, sortBy, {
            path: '/',
            expires: 365
        });

        url = addQueryStringParameter(url, 'rowCount', rowCount);
        url = addQueryStringParameter(url, 'sortBy', sortBy);
        url = addQueryStringParameter(url, 'current', page);
        $.get(url, function (response) {
            var videosList = $($.parseHTML(response)).filter("#videosListItems").html();
            loadVideosListPageIsLoading = false;
            $('#videosListItems').html(videosList);
            //animateChilds('#videosListItems', 'animate__flipInX', 0.2);
            lazyImage();
            avideoSocket();
            loadVideosListPageTransformLinks();
            modal.hidePleaseWait();
        });
    }
    function loadVideosListPageTransformLinks() {
        $('#videosListItems > nav a').click(function (event) {
            event.preventDefault();
            loadVideosListPage($(this).attr('pageNum'));
        });
    }
    function videosListDidNotSearchForVideos(){
        var rowCount = Cookies.get(loadVideosListPagerowCount);
        if(!empty(rowCount)){
            $('#rowCount').val(rowCount);
        }
        var sortBy = Cookies.get(loadVideosListPagesortBy);
        if(!empty(sortBy)){
            $('#sortBy').val(sortBy).trigger('change');
        }
        loadVideosListPage(1);
    }
</script>