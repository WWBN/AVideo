<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'objects/video.php';
$post = $_POST;
if (!empty($_POST['video_id'])) {
    $video = Video::getVideo($_POST['video_id'], "viewable");
}
$_POST = $post;

$catLink = "";
if (!empty($_GET['catName'])) {
    $catLink = "cat/{$_GET['catName']}/";
}

if (empty($_GET['page'])) {
    $_GET['page'] = 1;
} else {
    $_GET['page'] = intval($_GET['page']);
}
$_POST['current'] = $_GET['page'];

if (empty($_POST['rowCount'])) {
    if (!empty($_SESSION['rowCount'])) {
        $_POST['rowCount'] = $_SESSION['rowCount'];
    } else {
        $_POST['rowCount'] = 10;
    }
}
if (empty($_POST['sort'])) {
    if (!empty($_SESSION['sort'])) {
        $_POST['sort'] = $_SESSION['sort'];
    } else {
        $_POST['sort']['created'] = 'desc';
    }
}
$_SESSION['rowCount'] = $_POST['rowCount'];
$_SESSION['sort'] = $_POST['sort'];


$videos = Video::getAllVideos("viewableNotUnlisted");
$total = Video::getTotalVideos("viewableNotUnlisted");
$totalPages = ceil($total / $_POST['rowCount']);
$_POST = $post;
if (empty($totalPages)) {
    $totalPages = 1;
}
$videoName = "";
if (!empty($video['clean_title'])) {
    $videoName = $video['clean_title'];
} else if (!empty($_GET['videoName'])) {
    $videoName = $_GET['videoName'];
}
$get = array();

$get = array('channelName' => @$_GET['channelName'], 'catName' => @$_GET['catName']);
if (!empty($_GET['channelName']) && empty($advancedCustomUser->hideRemoveChannelFromModeYoutube)) {
    $user = User::getChannelOwner($_GET['channelName']);
//var_dump($user);exit;
    ?>
    <div class="col-md-12" >
        <img src="<?php echo User::getPhoto($user['id']); ?>" class="img img-responsive img-circle" style="max-width: 60px;"/>
        <div style="position: absolute; right: 5px; top: 5px;">
            <button class="btn btn-default btn-xs btn-sm" onclick="loadPage(<?php echo $_GET['page']; ?>, true);"><?php echo User::getNameIdentificationById($user['id']); ?> <i class="fa fa-times"></i></button>
        </div>
    </div>
    <?php
}
?>
<div class="col-md-8 col-sm-12 " style="position: relative; z-index: 2;" >
    <select class="form-control" id="sortBy" >
        <option value="titleAZ" data-icon="glyphicon-sort-by-attributes" <?php echo (!empty($_POST['sort']['title']) && strtolower($_POST['sort']['title']) == 'asc') ? "selected='selected'" : "" ?>> <?php echo __("Title (A-Z)"); ?></option>
        <option value="titleZA" data-icon="glyphicon-sort-by-attributes-alt" <?php echo (!empty($_POST['sort']['title']) && strtolower($_POST['sort']['title']) == 'desc') ? "selected='selected'" : "" ?>> <?php echo __("Title (Z-A)"); ?></option>
        <option value="newest" data-icon="glyphicon-sort-by-attributes" <?php echo (!empty($_POST['sort']['created']) && strtolower($_POST['sort']['created']) == 'desc') ? "selected='selected'" : "" ?>> <?php echo __("Date added (newest)"); ?></option>
        <option value="oldest" data-icon="glyphicon-sort-by-attributes-alt" <?php echo (!empty($_POST['sort']['created']) && strtolower($_POST['sort']['created']) == 'asc') ? "selected='selected'" : "" ?>> <?php echo __("Date added (oldest)"); ?></option>
        <option value="popular" data-icon="glyphicon-thumbs-up"  <?php echo (!empty($_POST['sort']['likes'])) ? "selected='selected'" : "" ?>> <?php echo __("Most popular"); ?></option>
        <?php
        if (empty($advancedCustom->doNotDisplayViews)) {
            ?> 
            <option value="views_count" data-icon="glyphicon-eye-open"  <?php echo (!empty($_POST['sort']['views_count'])) ? "selected='selected'" : "" ?>> <?php echo __("Most watched"); ?></option>
        <?php } ?>
    </select>
</div>
<div class="col-md-4 col-sm-12" style="position: relative; z-index: 2;">
    <select class="form-control" id="rowCount">
        <option <?php echo (!empty($_POST['rowCount']) && $_POST['rowCount'] == '10') ? "selected='selected'" : "" ?>>10</option>
        <option <?php echo (!empty($_POST['rowCount']) && $_POST['rowCount'] == '20') ? "selected='selected'" : "" ?>>20</option>
        <option <?php echo (!empty($_POST['rowCount']) && $_POST['rowCount'] == '30') ? "selected='selected'" : "" ?>>30</option>
        <option <?php echo (!empty($_POST['rowCount']) && $_POST['rowCount'] == '40') ? "selected='selected'" : "" ?>>40</option>
        <option <?php echo (!empty($_POST['rowCount']) && $_POST['rowCount'] == '50') ? "selected='selected'" : "" ?>>50</option>
    </select>
</div>

<?php
foreach ($videos as $key => $value) {
    if (!empty($video['id']) && $video['id'] == $value['id']) {
        continue; // skip video
    }
    $name = User::getNameIdentificationById($value['users_id']).' '. User::getEmailVerifiedIcon($value['users_id']);
    $value['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($value['users_id']) . '" alt="" class="img img-responsive img-circle zoom" style="max-width: 20px;"/></div><div class="commentDetails" style="margin-left:25px;"><div class="commenterName text-muted"><strong>' . $name . '</strong> <small>' . humanTiming(strtotime($value['videoCreation'])) . '</small></div></div>';
    ?>
    <div class="col-lg-12 col-sm-12 col-xs-12 bottom-border" id="divVideo-<?php echo $value['id']; ?>" itemscope itemtype="http://schema.org/VideoObject">
        <a href="<?php
        $link = Video::getLink($value['id'], $value['clean_title'], "", $get);
        $connection = "?";
        if (strpos($link, '?') !== false) {
            $connection = "&";
        }
        if (!empty($_GET['page']) && $_GET['page'] > 1) {
            $link .= "{$connection}page={$_GET['page']}";
        }
        echo $link;
        ?>" title="<?php echo $value['title']; ?>" class="videoLink h6">
            <div class="col-lg-5 col-sm-5 col-xs-5 nopadding thumbsImage" >
                <?php
                $images = Video::getImageFromFilename($value['filename'], $value['type']);

                if (!is_object($images)) {
                    $images = new stdClass();
                    $images->thumbsGif = "";
                    $images->poster = "{$global['webSiteRootURL']}view/img/notfound.jpg";
                    $images->thumbsJpg = "{$global['webSiteRootURL']}view/img/notfoundThumbs.jpg";
                    $images->thumbsJpgSmall = "{$global['webSiteRootURL']}view/img/notfoundThumbsSmall.jpg";
                }

                $imgGif = $images->thumbsGif;
                $img = $images->thumbsJpg;
                if (!empty($images->posterPortrait) && basename($images->posterPortrait) !== 'notfound_portrait.jpg' && basename($images->posterPortrait) !== 'pdf_portrait.png' && basename($images->posterPortrait) !== 'article_portrait.png') {
                    $imgGif = $images->gifPortrait;
                    $img = $images->posterPortrait;
                }
                if (($value['type'] !== "audio") && ($value['type'] !== "linkAudio")) {
                    $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
                } else {
                    $img_portrait = "";
                }
                ?>
                <div style="position: relative;">
                    <img src="<?php echo $images->thumbsJpgSmall; ?>" data-src="<?php echo $img; ?>" alt="<?php echo $value['title']; ?>" class="thumbsJPG img-responsive text-center <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>  <?php echo ($img != $images->thumbsJpgSmall) ? "blur" : ""; ?>" height="130" />
                    <?php
                    if (!empty($imgGif)) {
                        ?>
                        <img src="<?php echo $global['webSiteRootURL']; ?>view/img/loading-gif.png" data-src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
                    <?php } ?>
                    <meta itemprop="thumbnailUrl" content="<?php echo $img; ?>" />
                    <meta itemprop="uploadDate" content="<?php echo $value['created']; ?>" />
                    <?php
                    if ($video['type'] !== 'pdf' && $video['type'] !== 'article') {
                        ?>
                        <time class="duration" itemprop="duration" datetime="<?php echo Video::getItemPropDuration($value['duration']); ?>"><?php echo Video::getCleanDuration($value['duration']); ?></time>
                        <?php
                    }
                    ?>
                </div>
                <div class="progress" style="height: 3px; margin-bottom: 2px;">
                    <div class="progress-bar progress-bar-danger" role="progressbar" style="width: <?php echo $value['progress']['percent'] ?>%;" aria-valuenow="<?php echo $value['progress']['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
            <div class="col-lg-7 col-sm-7 col-xs-7 videosDetails">
                <div class="text-uppercase row"><strong itemprop="name" class="title"><?php echo $value['title']; ?></strong></div>
                <div class="details row" itemprop="description">
                    <div>
                        <strong><?php echo __("Category"); ?>: </strong>
                        <span class="<?php echo $value['iconClass']; ?>"></span>
                        <?php echo $value['category']; ?>
                    </div>
                    <?php
                    if (empty($advancedCustom->doNotDisplayViews)) {
                        ?>
                        <div>
                            <strong class="view-count<?php echo $value['id']; ?>"><?php echo number_format($value['views_count'], 0); ?></strong> <?php echo __("Views"); ?>
                        </div>
                    <?php } ?>
                    <div><?php echo $value['creator']; ?></div>

                </div>
                <div class="row">
                    <?php
                    foreach ($value['tags'] as $value2) {
                        if ($value2->label === __("Group")) {
                            ?>
                            <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </a>
        <?php
        getLdJson($value['id']);
        getItemprop($value['id']);
        ?>
    </div>
    <?php
}
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
        var query = "";
<?php
if (!empty($get)) {
    echo "query = \"?" . http_build_query($get) . "\";";
}
?>
        if (disableChannel) {
            query = "";
        }

        var url = '<?php echo $global['webSiteRootURL'], $catLink; ?>video/<?php echo $videoName; ?>' + page + query;
                var urlList = "<?php echo $global['webSiteRootURL']; ?>videosList/<?php echo $catLink; ?>video/<?php echo $videoName; ?>" + page + query;


                        history.pushState(null, null, url);
                        $('.pages').slideUp();
                        $('#pageLoader').fadeIn();
                        rowCount = $('#rowCount').val();
                        sortBy = $('#sortBy').val();
                        console.log(sortBy);
                        if (sortBy == 'newest') {
                            sortBy = {'created': 'desc'};
                        } else
                        if (sortBy == 'oldest') {
                            sortBy = {'created': 'asc'};
                        } else if (sortBy == 'views_count') {
                            sortBy = {'views_count': 'desc'};
                        } else if (sortBy == 'titleAZ') {
                            sortBy = {'title': 'asc'};
                        } else if (sortBy == 'titleZA') {
                            sortBy = {'title': 'desc'};
                        } else {
                            sortBy = {'likes': 'desc'};
                        }
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
include $global['systemRootPath'] . 'objects/include_end.php';
?>