<?php
require_once dirname(__FILE__) . '/../videos/configuration.php';

require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'objects/video.php';

$catLink = "";
if (!empty($_GET['catName'])) {
    $catLink = "cat/{$_GET['catName']}/";
}

if (empty($_GET['page'])) {
    $_GET['page'] = 1;
} else {
    $_GET['page'] = intval($_GET['page']);
}
$_POST['rowCount'] = 10;
$_POST['current'] = $_GET['page'];
$_POST['sort']['created'] = 'desc';

$videos = Video::getAllVideos("viewableNotAd");
$total = Video::getTotalVideos("viewableNotAd");
$totalPages = ceil($total / $_POST['rowCount']);

$videoName = "";
if (!empty($video['clean_title'])) {
    $videoName = $video['clean_title'];
} else if (!empty($_GET['videoName'])) {
    $videoName = $_GET['videoName'];
}

?>
<!--
<select class="form-control" id="sortBy">
    <option value="newest"><?php echo __("Date Added (newest)"); ?></option>
    <option value="oldest"><?php echo __("Date Added (oldest)"); ?></option>
    <option value="popular"><?php echo __("Most Popular"); ?></option>
</select>
<select class="form-control" id="rowCount">
    <option>10</option>
    <option>20</option>
    <option>30</option>
    <option>50</option>
</select>
-->
<?php

foreach ($videos as $key => $value) {
    if (!empty($video['id']) && $video['id'] == $value['id']) {
        continue; // skip video
    }
    $name = empty($value['name']) ? $value['user'] : $value['name'];
    $value['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($value['users_id']) . '" alt="" class="img img-responsive img-circle" style="max-width: 20px;"/></div><div class="commentDetails" style="margin-left:25px;"><div class="commenterName text-muted"><strong>' . $name . '</strong> <small>' . humanTiming(strtotime($value['videoCreation'])) . '</small></div></div>';
    ?>
    <div class="col-lg-12 col-sm-12 col-xs-12 bottom-border" itemscope itemtype="http://schema.org/VideoObject">
        <a href="<?php echo $global['webSiteRootURL'], $catLink; ?>video/<?php
        echo $value['clean_title'];
        if (!empty($_GET['page']) && $_GET['page'] > 1) {
            echo "/page/{$_GET['page']}";
        }
        ?>" title="<?php echo $value['title']; ?>" class="videoLink">
            <div class="col-lg-5 col-sm-5 col-xs-5 nopadding thumbsImage" >
                <?php
                $imgGif = "";
                if (file_exists("{$global['systemRootPath']}videos/{$value['filename']}.gif")) {
                    $imgGif = "{$global['webSiteRootURL']}videos/{$value['filename']}.gif";
                }
                if ($value['type'] !== "audio") {
                    $img = "{$global['webSiteRootURL']}videos/{$value['filename']}.jpg";
                    $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
                } else {
                    $img = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
                    $img_portrait = "";
                }
                ?>
                <img src="<?php echo $img; ?>" alt="<?php echo $value['title']; ?>" class="thumbsJPG img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
                <?php
                if (!empty($imgGif)) {
                    ?>
                    <img src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
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
                        <strong class=""><?php echo number_format($value['views_count'], 0); ?></strong> <?php echo __("Views"); ?>
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
    function setBootPage() {
        $('.pages').bootpag({
            total: <?php echo $totalPages; ?>,
            page: <?php echo $_GET['page']; ?>,
            maxVisible: 10
        }).on('page', function (event, num) {
            history.pushState(null, null, '<?php echo $global['webSiteRootURL'], $catLink; ?>video/<?php echo $videoName; ?>/page/' + num);
            $('.pages').slideUp();
            $('#pageLoader').fadeIn();
            $("#videosList").load("<?php echo $global['webSiteRootURL']; ?>videosList/video/<?php echo $videoName; ?>/page/" + num, function () {
                setBootPage();
            });
        });
    }
    
    $(document).ready(function () {
        setBootPage();
        mouseEffect();
    });
</script>