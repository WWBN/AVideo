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
<link rel="stylesheet" href="<?php echo getURL('view/css/videosList.css'); ?>">
<div class="videosListToolbar">
<div class="videosListSort">
    <label for="sortBy" class="text-muted"><?php echo __('Sort by'); ?></label>
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
<div class="videosListCount">
    <label for="rowCount" class="text-muted"><?php echo __('Videos'); ?></label>
    <select class="form-control" id="rowCount">
        <?php
        foreach ($jsonRowCountArray as $item) {
            if ($item == -1) {
                ?>
                <option value="-1" <?php echo (!empty($_REQUEST['rowCount']) && $_REQUEST['rowCount'] == $item) ? "selected='selected'" : "" ?>><?php echo __("All"); ?></option>
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
</div>
<div id="videosListStatus" class="text-muted" role="status" aria-live="polite">
    <span class="videosListLoadingText"><?php echo __('Loading...'); ?></span>
    <span class="videosListErrorText" hidden><?php echo __('Could not load videos. Please try again.'); ?></span>
    <button type="button" class="btn btn-default btn-xs videosListRetry" hidden><?php echo __('Retry'); ?></button>
</div>
<div id="videosListItems" aria-busy="<?php echo $searchForVideosNow ? 'false' : 'true'; ?>">
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
        if ($getVideosListItem === '') {
            echo '<div class="videosListEmpty text-muted"><i class="fas fa-film" aria-hidden="true"></i><p>' . __('No videos found') . '</p></div>';
        } else {
            echo $getVideosListItem;
        }
        //var_dump(getRowCount(), $totalPages, getCurrentPage(), $link);
        echo getPagination($totalPages, $link, 5);
    } else {
        for ($i = 0; $i < 3; $i++) {
        ?>
        <div class="loadingVideosList videoListItem" aria-hidden="true">
            <div class="videosListSkeletonThumb videosListSkeletonBlock loading-background"></div>
            <div class="videosListSkeletonDetails">
                <div class="videosListSkeletonBlock loading-background"></div>
                <div class="videosListSkeletonBlock loading-background"></div>
                <div class="videosListSkeletonBlock loading-background"></div>
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
        $('#videosListStatus .videosListRetry').on('click', function () {
            loadVideosListPage(loadVideosListLastPage);
        });
        $('#videosListStatus .videosListLoadingText').prop('hidden', $('#videosListItems').attr('aria-busy') !== 'true');
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
    var loadVideosListPendingPage = null;
    var loadVideosListLastPage = 1;

    function loadVideosListPage(page) {
        clearTimeout(loadVideosListPageTimeout);
        if (typeof modal === 'undefined') {
            loadVideosListPageTimeout = setTimeout(function () {
                loadVideosListPage(page);
            }, 500);
            return false;
        }
        if (loadVideosListPageIsLoading) {
            loadVideosListPendingPage = page;
            return false;
        }
        loadVideosListLastPage = page;
        loadVideosListPageIsLoading = true;
        $('#videosListItems').attr('aria-busy', 'true');
        $('#videosListStatus .videosListLoadingText').prop('hidden', false);
        $('#videosListStatus .videosListErrorText, #videosListStatus .videosListRetry').prop('hidden', true);
        var url = '<?php echo $link; ?>';

        var rowCount = $('#rowCount').val();
        var sortBy = $('#sortBy').val();

        Cookies.set(loadVideosListPagerowCount, rowCount, avideoCookieOptions(365));
        Cookies.set(loadVideosListPagesortBy, sortBy, avideoCookieOptions(365));

        url = addQueryStringParameter(url, 'rowCount', rowCount);
        url = addQueryStringParameter(url, 'sortBy', sortBy);
        url = addQueryStringParameter(url, 'current', page);
        $.ajax({url: url, timeout: 30000}).done(function (response) {
            if (loadVideosListPendingPage !== null) {
                return;
            }
            var videosList = $($.parseHTML(response)).filter('#videosListItems');
            if (!videosList.length) {
                videosListLoadFailed();
                return;
            }
            $('#videosListItems').html(videosList.html());
            lazyImage();
            avideoSocket();
            loadVideosListPageTransformLinks();
        }).fail(function () {
            if (loadVideosListPendingPage === null) {
                videosListLoadFailed();
            }
        }).always(function () {
            loadVideosListPageIsLoading = false;
            if (loadVideosListPendingPage !== null) {
                var nextPage = loadVideosListPendingPage;
                loadVideosListPendingPage = null;
                loadVideosListPage(nextPage);
                return;
            }
            $('#videosListItems').attr('aria-busy', 'false');
            $('#videosListStatus .videosListLoadingText').prop('hidden', true);
        });
    }
    function videosListLoadFailed() {
        $('#videosListItems .loadingVideosList').remove();
        $('#videosListStatus .videosListErrorText, #videosListStatus .videosListRetry').prop('hidden', false);
    }

    function loadVideosListPageTransformLinks() {
        // Pagination's default inline handler opens a blocking modal for navigation.
        // This list loads in place and owns its local loading indicator instead.
        $('#videosListItems > nav a').removeAttr('onclick').off('click.videosList').on('click.videosList', function (event) {
            event.preventDefault();
            event.stopPropagation();
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
            $('#sortBy').val(sortBy).trigger('change.select2');
        }
        loadVideosListPage(1);
    }
</script>
