<?php
require_once dirname(__FILE__) . '/../videos/configuration.php';

require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'objects/video.php';

if (!empty($_POST['video_id'])) {
    $video = Video::getVideo($_POST['video_id'], "viewableNotAd");
}

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


$videos = Video::getAllVideos("viewableNotAd");
$total = Video::getTotalVideos("viewableNotAd");
$totalPages = ceil($total / $_POST['rowCount']);
if (empty($totalPages)) {
    $totalPages = 1;
}
$videoName = "";
if (!empty($video['clean_title'])) {
    $videoName = $video['clean_title'];
} else if (!empty($_GET['videoName'])) {
    $videoName = $_GET['videoName'];
}
?>
<div class="col-md-8 col-sm-12 " style="position: relative; z-index: 2;" >
    <select class="form-control" id="sortBy" >
        <option value="title" data-icon="glyphicon-text-height" value="desc" <?php echo (!empty($_POST['sort']['title']) && $_POST['sort']['title'] == 'asc') ? "selected='selected'" : "" ?>> <?php echo __("Title"); ?></option>
        <option value="newest" data-icon="glyphicon-sort-by-attributes" value="desc" <?php echo (!empty($_POST['sort']['created']) && $_POST['sort']['created'] == 'desc') ? "selected='selected'" : "" ?>> <?php echo __("Date added (newest)"); ?></option>
        <option value="oldest" data-icon="glyphicon-sort-by-attributes-alt" value="asc" <?php echo (!empty($_POST['sort']['created']) && $_POST['sort']['created'] == 'asc') ? "selected='selected'" : "" ?>> <?php echo __("Date added (oldest)"); ?></option>
        <option value="popular" data-icon="glyphicon-thumbs-up"  <?php echo (!empty($_POST['sort']['likes'])) ? "selected='selected'" : "" ?>> <?php echo __("Most popular"); ?></option>
        <option value="views_count" data-icon="glyphicon-eye-open"  <?php echo (!empty($_POST['sort']['views_count'])) ? "selected='selected'" : "" ?>> <?php echo __("Most watched"); ?></option>
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
    $name = User::getNameIdentificationById($value['users_id']);
    $value['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($value['users_id']) . '" alt="" class="img img-responsive img-circle zoom" style="max-width: 20px;"/></div><div class="commentDetails" style="margin-left:25px;"><div class="commenterName text-muted"><strong>' . $name . '</strong> <small>' . humanTiming(strtotime($value['videoCreation'])) . '</small></div></div>';
    ?>
    <div class="col-lg-12 col-sm-12 col-xs-12 bottom-border" id="divVideo-<?php echo $value['id']; ?>" itemscope itemtype="http://schema.org/VideoObject">
        <a href="<?php echo $global['webSiteRootURL'], $catLink; ?>video/<?php
        echo $value['clean_title'];
        if (!empty($_GET['page']) && $_GET['page'] > 1) {
            echo "/page/{$_GET['page']}";
        }
        ?>" title="<?php echo $value['title']; ?>" class="videoLink h6">
            <div class="col-lg-5 col-sm-5 col-xs-5 nopadding thumbsImage" >
                <?php
                $images = Video::getImageFromFilename($value['filename'], $value['type']);

                $imgGif = $images->thumbsGif;
                $img = $images->thumbsJpg;
                if ($value['type'] !== "audio") {
                    $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
                } else {
                    $img_portrait = "";
                }
                ?>
                <img src="<?php echo $images->thumbsJpgSmall; ?>" data-src="<?php echo $img; ?>" alt="<?php echo $value['title']; ?>" class="thumbsJPG img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>  <?php echo ($img!=$images->thumbsJpgSmal)?"blur":""; ?>" height="130" />
                <?php
                if (!empty($imgGif)) {
                    ?>
                    <img src="<?php echo $global['webSiteRootURL']; ?>img/loading-gif.png" data-src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
                <?php } ?>
                <meta itemprop="thumbnailUrl" content="<?php echo $img; ?>" />
                <meta itemprop="uploadDate" content="<?php echo $value['created']; ?>" />
                <time class="duration" itemprop="duration" datetime="<?php echo Video::getItemPropDuration($value['duration']); ?>"><?php echo Video::getCleanDuration($value['duration']); ?></time>
            </div>
            <div class="col-lg-7 col-sm-7 col-xs-7 videosDetails">
                <div class="text-uppercase row"><strong itemprop="name" class="title"><?php echo $value['title']; ?></strong></div>
                <div class="details row" itemprop="description">
                    <div>
                        <strong><?php echo __("Category"); ?>: </strong>
                        <span class="<?php echo $value['iconClass']; ?>"></span>
                        <?php echo $value['category']; ?>
                    </div>
                    <div>
                        <strong class="view-count<?php echo $value['id']; ?>"><?php echo number_format($value['views_count'], 0); ?></strong> <?php echo __("Views"); ?>
                    </div>
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
            loadPage(num);
        });
    }

    function loadPage(num) {
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

        history.pushState(null, null, '<?php echo $global['webSiteRootURL'], $catLink; ?>video/<?php echo $videoName; ?>' + page);
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
                } else if (sortBy == 'title') {
                    sortBy = {'title': 'asc'};
                } else {
                    sortBy = {'likes': 'desc'};
                }
                $.ajax({
                    type: "POST",
                    url: "<?php echo $global['webSiteRootURL']; ?>videosList/<?php echo $catLink; ?>video/<?php echo $videoName; ?>" + page,
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
                                loadPage(num);
                            });
                            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent)) {
                                $('#rowCount, #sortBy').selectpicker('mobile');
                            } else {
                                $('#rowCount, #sortBy').selectpicker();
                            }

                            $('.thumbsJPG, .thumbsGIF').lazy({
                                effect: 'fadeIn',
                                visibleOnly: true
                            });
                        });
</script>
<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>