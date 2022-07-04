<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
if (isBot()) {
    return;
}

$TimeLogLimitVL = 0.01;
$timeLogNameVL = TimeLogStart("videosList.php");

$post = $_POST;
if (!empty($_POST['video_id'])) {
    $video = Video::getVideo($_POST['video_id'], "viewable");
}
$_POST = $post;

TimeLogEnd($timeLogNameVL, __LINE__, $TimeLogLimitVL);
$catLink = '';
if (!empty($_GET['catName'])) {
    $catLink = "cat/{$_GET['catName']}/";
}

if (empty($_GET['page'])) {
    $_GET['page'] = 1;
} else {
    $_GET['page'] = intval($_GET['page']);
}
$_REQUEST['current'] = $_GET['page'];

if (empty($_REQUEST['rowCount'])) {
    if (!empty($_SESSION['rowCount'])) {
        $_REQUEST['rowCount'] = $_SESSION['rowCount'];
    } else {
        $_REQUEST['rowCount'] = 10;
    }
}

if ($_REQUEST['rowCount'] <= 0 || $_REQUEST['rowCount'] > 100) {
    $_REQUEST['rowCount'] = 10;
}

$sortOptions = array(
    array('key' => 'title', 'order' => 'asc', 'sortBy' => 'titleAZ', 'label' => __("Title (A-Z)"), 'data-icon' => 'glyphicon-sort-by-attributes'),
    array('key' => 'title', 'order' => 'desc', 'sortBy' => 'titleZA', 'label' => __("Title (Z-A)"), 'data-icon' => 'glyphicon-sort-by-attributes-alt'),
    array('key' => 'created', 'order' => 'desc', 'sortBy' => 'newest', 'label' => __("Date added (newest)"), 'data-icon' => 'glyphicon-sort-by-attributes'),
    array('key' => 'created', 'order' => 'asc', 'sortBy' => 'oldest', 'label' => __("Date added (oldest)"), 'data-icon' => 'glyphicon-sort-by-attributes-alt'),
    array('key' => 'likes', 'order' => 'desc', 'sortBy' => 'popular', 'label' => __("Most popular"), 'data-icon' => 'glyphicon-thumbs-up'),
    array('key' => 'suggested', 'order' => 'desc', 'sortBy' => 'suggested', 'label' => __("Suggested"), 'data-icon' => 'glyphicon-star'),
);

if (empty($advancedCustom->doNotDisplayViews)) {
    $sortOptions[] = array('key' => 'views_count', 'order' => 'desc', 'sortBy' => 'views_count', 'label' => __("Most watched"), 'data-icon' => 'glyphicon-eye-open');
}

$sortBy = $advancedCustom->sortVideoListByDefault->value;
if (empty($_POST['sort']) && !empty($_SESSION['sort'])) {
    $_POST['sort'] = $_SESSION['sort'];
}
if (!empty($_POST['sort'])) {
    foreach ($sortOptions as $value) {
        if (!empty($_POST['sort'][$value['key']])) {
            $order = strtolower($_POST['sort'][$value['key']]);
            if ($order === strtolower($value['order'])) {
                $sortBy = $value['sortBy'];
                break;
            }
        }
    }
} else {
    $_POST['sort'] = array();
    foreach ($sortOptions as $value) {
        $sortBy = strtolower($sortBy);
        if ($sortBy === strtolower($value['order'])) {
            $_POST['sort'][$value['key']] = $value['order'];
            break;
        }
    }
}

$_SESSION['rowCount'] = $_REQUEST['rowCount'];
$_SESSION['sort'] = $_POST['sort'];

if(!empty($_POST['sort']['undefined'])){
    unset($_POST['sort']['undefined']);
}

//var_dump($sortBy, $_POST['sort'], strtolower($_POST['sort']['created']) == 'desc');

TimeLogEnd($timeLogNameVL, __LINE__, $TimeLogLimitVL);
$videos = Video::getAllVideos("viewableNotUnlisted");
$total = Video::getTotalVideos("viewableNotUnlisted");
TimeLogEnd($timeLogNameVL, __LINE__, $TimeLogLimitVL);
$totalPages = ceil($total / $_REQUEST['rowCount']);
$_POST = $post;
if (empty($totalPages)) {
    $totalPages = 1;
}
$videoName = '';
if (!empty($video['clean_title'])) {
    $videoName = $video['clean_title'];
} elseif (!empty($_GET['videoName'])) {
    $videoName = $_GET['videoName'];
}
$get = [];

$get = ['channelName' => @$_GET['channelName'], 'catName' => @$_GET['catName']];
if (!empty($_GET['channelName']) && empty($advancedCustomUser->hideRemoveChannelFromModeYoutube)) {
    $user = User::getChannelOwner($_GET['channelName']);
    //var_dump($user);exit;
    ?>
    <div class="col-md-12" style="padding: 15px; margin: 5px 0; background-image: url(<?php echo $global['webSiteRootURL'], User::getBackgroundURLFromUserID($user['id']); ?>); background-size: cover;"  >
        <img src="<?php echo User::getPhoto($user['id']); ?>" class="img img-responsive img-circle" style="max-width: 60px;" alt="User Photo"/>
        <div style="position: absolute; right: 5px; top: 5px;">
            <button class="btn btn-default btn-xs btn-sm" onclick="loadPage(<?php echo $_GET['page']; ?>, true);"><?php echo User::getNameIdentificationById($user['id']); ?> <i class="fa fa-times"></i></button>
        </div>
    </div>
    <?php
}

$objGallery = AVideoPlugin::getObjectData("Gallery");
if (empty($video['id'])) {
    $video['id'] = 0;
}
TimeLogEnd($timeLogNameVL, __LINE__, $TimeLogLimitVL);
?>
<div class="col-md-8 col-sm-12 " style="position: relative; z-index: 10;" >
    <select class="form-control" id="sortBy" >
        <?php
        foreach ($sortOptions as $value) {
            ?>
            <option 
                value="<?php echo $value['sortBy']; ?>" 
                data-icon="<?php echo $value['data-icon']; ?>" 
                order="<?php echo $value['order']; ?>" 
                key="<?php echo $value['key']; ?>" 
                <?php echo ($sortBy === $value['sortBy']) ? "selected='selected'" : "" ?>> 
                    <?php echo $value['label']; ?>
            </option>
            <?php
        }
        ?>
    </select>
</div>
<div class="col-md-4 col-sm-12" style="position: relative; z-index: 2;">
    <select class="form-control" id="rowCount">
        <?php
        $jsonArray = _json_decode($advancedCustom->videosListRowCount);
        foreach ($jsonArray as $item) {
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

<?php
TimeLogEnd($timeLogNameVL, __LINE__, $TimeLogLimitVL);

$cacheName = "videosList_" . md5(json_encode($_REQUEST));
$getVideosListItem = ObjectYPT::getSessionCache($cacheName);
//$program = AVideoPlugin::loadPluginIfEnabled('PlayLists');
if (empty($getVideosListItem)) {
    $getVideosListItem = '';
    foreach ($videos as $key => $value) {
        if (!empty($video['id']) && $video['id'] == $value['id']) {
            continue; // skip video
        }
        $getVideosListItem .= Video::getVideosListItem($value['id']);
    }
    ObjectYPT::setSessionCache($cacheName, $getVideosListItem);
}
echo $getVideosListItem;
TimeLogEnd($timeLogNameVL, __LINE__, $TimeLogLimitVL);
?>
<ul class="pages">
</ul>
<div class="loader" id="pageLoader" style="display: none;"></div>
<script>
    var isLoadingPage = 0;
    function setBootPage() {
        $('.pages').bootpag({
            total: <?php echo $totalPages; ?>,
            page: <?php echo $_GET['page']; ?>,
            maxVisible: 10
        }).on('page', function (event, num) {
            loadPage(num, false);
        });
    }

    function loadPage(num, disableChannel) {
        if (isLoadingPage) {
            return false;
        }
        isLoadingPage = 1;
        $("#videosList").find('a').click(false);
        $("#videosList").addClass('transparent');
        console.log(num);
        var page = '/page/1';
        if (typeof num != 'undefined' && num != 'undefined') {
            page = '/page/' + num;
        }
        var query = '';
<?php
if (!empty($get)) {
    echo "query = \"?" . http_build_query($get) . "\";";
}
?>
        if (disableChannel) {
            query = '';
        }
<?php
if (!empty($videoName) && !empty($video['id'])) {
    ?>
            var url = '<?php echo $global['webSiteRootURL'], addslashes($catLink); ?>video/<?php echo addslashes($videoName); ?>' + page + query;
    <?php
} elseif (!empty($_GET['evideo'])) {
    ?>
                    var url = '<?php echo $global['webSiteRootURL'], addslashes($catLink); ?>evideo/<?php echo $_GET['evideo']; ?>';
    <?php
} else {
    ?>
                            var url = '<?php echo $global['webSiteRootURL'], addslashes($catLink); ?>';
    <?php
}
?>
                        var urlList = "<?php echo $global['webSiteRootURL']; ?>videosList/<?php echo addslashes($catLink); ?>video/<?php echo addslashes($videoName); ?>" + page + query;


                                history.pushState(null, null, url);
                                $('.pages').slideUp();
                                $('#pageLoader').fadeIn();
                                rowCount = $('#rowCount').val();
                                
                                var key = $('#sortBy option:selected').attr('key');
                                var order = $('#sortBy option:selected').attr('order');
                                
                                eval("sortBy = {'"+key+"': '"+order+"'};");
        
                                console.log(sortBy);
        
                                $.ajax({
                                    type: "POST",
                                    url: urlList,
                                    data: {
                                        rowCount: rowCount,
                                        sort: sortBy,
                                        video_id: <?php echo $video['id']; ?>
                                    }
                                }).done(function (result) {
                                    $("#videosList").html(result);
                                    setBootPage();
                                    $("#videosList").removeClass('transparent');
                                });
                            }

                            $(document).ready(function () {
                                setBootPage();
                                mouseEffect();
                                $('#rowCount, #sortBy').change(function () {
                                    num = $('#videosList').find('.pagination').find('li.active').attr('data-lp');
                                    loadPage(num, false);
                                });
                                if (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent)) {
                                    $('#rowCount, #sortBy').selectpicker('mobile');
                                } else {
                                    $('#rowCount, #sortBy').selectpicker();
                                }

                                $('.thumbsJPG').lazy({
                                    effect: 'fadeIn',
                                    visibleOnly: true,
                                    // called after an element was successfully handled
                                    afterLoad: function (element) {
                                        element.removeClass('blur');
                                        element.parent().find('.thumbsGIF').lazy({
                                            effect: 'fadeIn'
                                        });
                                    }
                                });
                            });
</script>
<?php
//include $global['systemRootPath'] . 'objects/include_end.php';
?>
