<?php
if (!file_exists('../videos/configuration.php')) {
    if (!file_exists('../install/index.php')) {
        die("No Configuration and no Installation");
    }
    header("Location: install/index.php");
}

require_once '../videos/configuration.php';

require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

$obj = YouPHPTubePlugin::getObjectData("Gallery");

if (!empty($_GET['type'])) {
    if ($_GET['type'] == 'audio') {
        $_SESSION['type'] = 'audio';
    } else if ($_GET['type'] == 'video') {
        $_SESSION['type'] = 'video';
    } else {
        $_SESSION['type'] = "";
        unset($_SESSION['type']);
    }
}

require_once $global['systemRootPath'] . 'objects/video.php';
if($obj->sortReverseable){
    if(strpos($_SERVER['REQUEST_URI'],"?")!=false){
        $orderString = $_SERVER['REQUEST_URI']."&";
    } else {
        $orderString =  $_SERVER['REQUEST_URI']."/?";
    }
    $orderString = str_replace("&&","&",$orderString);
    $orderString = str_replace("//","/",$orderString);

    function createOrderInfo($getName,$mostWord,$lessWord,$orderString){
        $upDown = "";
        $mostLess = "";
        $tmpOrderString = $orderString;
        if($_GET[$getName]=="DESC"){
            if(strpos($orderString,$getName."=DESC")){
                $tmpOrderString =  substr($orderString,0,strpos($orderString,$getName."=DESC")).$getName."=ASC".substr($orderString,strpos($orderString,$getName."=DESC")+strlen($getName."=DESC"),strlen($orderString));
            } else {
                $tmpOrderString .= $getName."=ASC";
            }
                $upDown = "<span class='glyphicon glyphicon-arrow-up' >".__("Up")."</span>";
                $mostLess = $mostWord;
        } else {
            if(strpos($orderString,$getName."=ASC")){
                $tmpOrderString =  substr($orderString,0,strpos($orderString,$getName."=ASC")).$getName."=DESC".substr($orderString,strpos($orderString,$getName."=ASC")+strlen($getName."=ASC"),strlen($orderString));
            } else {
                $tmpOrderString .= $getName."=DESC";
            }
            $upDown = "<span class='glyphicon glyphicon-arrow-down'>".__("Down")."</span>";
            $mostLess = $lessWord;
        }
        if(substr($tmpOrderString,strlen($tmpOrderString)-1,strlen($tmpOrderString))=="&"){
                $tmpOrderString = substr($tmpOrderString,0,strlen($tmpOrderString)-1);
        }
        return array($tmpOrderString,$upDown,$mostLess);
    }
}
$video = Video::getVideo("", "viewableNotAd", false, false, true);
if (empty($video)) {
    $video = Video::getVideo("", "viewableNotAd");
}

if (empty($_GET['page'])) {
    $_GET['page'] = 1;
} else {
    $_GET['page'] = intval($_GET['page']);
}
$_POST['rowCount'] = 24;
$_POST['current'] = $_GET['page'];
$_POST['sort']['created'] = 'desc';
$videos = Video::getAllVideos("viewableNotAd");
foreach ($videos as $key => $value) {
    $name = empty($value['name']) ? $value['user'] : $value['name'];
    $videos[$key]['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($value['users_id']) . '" alt="" class="img img-responsive img-circle" style="max-width: 20px;"/></div><div class="commentDetails" style="margin-left:25px;"><div class="commenterName"><strong>' . $name . '</strong> <small>' . humanTiming(strtotime($value['videoCreation'])) . '</small></div></div>';
}
$total = Video::getTotalVideos("viewableNotAd");
$totalPages = ceil($total / $_POST['rowCount']);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <meta name="generator" content="YouPHPTube - A Free Youtube Clone Script" />
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <script>
            $(document).ready(function () {
                // Total Itens <?php echo $total; ?>

                $('.pages').bootpag({
                    total: <?php echo $totalPages; ?>,
                    page: <?php echo $_GET['page']; ?>,
                    maxVisible: 10
                }).on('page', function (event, num) {
                <?php
                    $url = '';
                    $args = '';
                    if(strpos($_SERVER['REQUEST_URI'],"?")!=false){
                        $args = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'],"?"), strlen($_SERVER['REQUEST_URI']));
                    }
		    echo 'var args = "'.$args.'";';
                    if (strpos($_SERVER['REQUEST_URI'], "/cat/") === false) {
                        $url = $global['webSiteRootURL'] . "page/";
                    } else {
                        $url = $global['webSiteRootURL'] . "cat/" . $video['clean_category'] . "/page/";
                    }
                ?>
                    window.location.replace("<?php echo $url; ?>" + num + args);
                });
            });
        </script>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>
        <div class="container-fluid gallery" itemscope itemtype="http://schema.org/VideoObject">
            <div class="row text-center" style="padding: 10px;">
                <?php
                echo $config->getAdsense();
                ?>
            </div>
            
            <div class="col-sm-10 col-sm-offset-1 list-group-item">
                <?php
                if (!empty($videos)) {
                    $name = User::getNameIdentificationById($video['users_id']);
                    $img_portrait = ($video['rotation'] === "90" || $video['rotation'] === "270") ? "img-portrait" : "";
                    ?>
                    <?php if (($obj->CategoryDescription)&&(!empty($_GET['catName']))) { ?>
                        <h1 style="text-align: center;"><?php echo $video['category']; ?></h1>
                        <p style="margin-left: 10%; margin-right: 10%; max-height: 200px; overflow-x:auto;"><?php echo $video['category_description']; ?></p>
                    <?php } ?>
                    <div class="row mainArea">

                        <?php if ($obj->BigVideo) { ?>
                            <div class="clear clearfix firstRow">
                                <div class="row thumbsImage">
                                    <div class="col-sm-6">
                                        <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $video['clean_category']; ?>/video/<?php echo $video['clean_title']; ?>" 
                                           title="<?php echo $video['title']; ?>" style="" >
                                               <?php
                                               $images = Video::getImageFromFilename($video['filename'], $video['type']);
                                               $imgGif = $images->thumbsGif;
                                               $poster = $images->poster;
                                               ?>                                        
                                            <div class="aspectRatio16_9">
                                                <img src="<?php echo $images->thumbsJpg; ?>" data-src="<?php echo $poster; ?>" alt="<?php echo $video['title']; ?>" class="thumbsJPG img img-responsive " style="height: auto; width: 100%;" id="thumbsJPG<?php echo $video['id']; ?>"  />

                                                <?php
                                                if (!empty($imgGif)) {
                                                    ?>
                                                    <img src="<?php echo $global['webSiteRootURL']; ?>img/loading-gif.png" data-src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $video['title']; ?>" id="thumbsGIF<?php echo $video['id']; ?>" class="thumbsGIF img-responsive <?php echo @$img_portrait; ?>  rotate<?php echo $video['rotation']; ?>" height="130" />
                                                <?php } ?>
                                            </div>
                                            <span class="duration"><?php echo Video::getCleanDuration($video['duration']); ?></span>
                                        </a>
                                    </div>
                                    <div class="col-sm-6">
                                        <a class="h6" href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $video['clean_title']; ?>" title="<?php echo $video['title']; ?>">
                                            <h1><?php echo $video['title']; ?></h1>
                                        </a>
                                        <div class="mainAreaDescriptionContainer">
                                            <h4 class="mainAreaDescription"  itemprop="description"><?php echo nl2br(textToLink($video['description'])); ?></h4>
                                        </div>
                                        <div class="text-muted galeryDetails">
                                            <div>
                                                <?php
                                                $value['tags'] = Video::getTags($video['id']);
                                                foreach ($value['tags'] as $value2) {
                                                    if ($value2->label === __("Group")) {
                                                        ?>
                                                        <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <div>
                                                <i class="fa fa-eye"></i>
                                                <span itemprop="interactionCount">
                                                    <?php echo number_format($video['views_count'], 0); ?> <?php echo __("Views"); ?>
                                                </span>
                                            </div>
                                            <div>
                                                <i class="fa fa-clock-o"></i>
                                                <?php
                                                echo humanTiming(strtotime($video['videoCreation'])), " ", __('ago');
                                                ?>
                                            </div>
                                            <div>
                                                <i class="fa fa-user"></i>
                                                <a class="text-muted" href="<?php echo $global['webSiteRootURL']; ?>channel/<?php echo $video['users_id']; ?>/">
                                                    <?php
                                                    echo $name;
                                                    ?>
                                                </a>
                                            </div>
                                            <?php
                                            if(Video::canEdit($video['id'])){
                                                ?>
                                                <div>
                                                    <a href="<?php echo $global['webSiteRootURL']; ?>mvideos?video_id=<?php echo $video['id']; ?>" class="text-primary"><i class="fa fa-edit"></i> <?php echo __("Edit Video"); ?></a>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div> 
                                </div>
                            </div>

                        <?php } 
                        ?>
                        <!-- For Live Videos -->
                        <div id="liveVideos" class="clear clearfix" style="display: none;">
                            <h3 class="galleryTitle text-danger">
                                <i class="fa fa-youtube-play"></i> <?php
                                echo __("Live");
                                ?>
                            </h3>
                            <div class="row extraVideos">

                            </div>
                        </div>
                        <script>
                            function afterExtraVideos($liveLi){
                                $liveLi.removeClass('col-lg-12 col-sm-12 col-xs-12 bottom-border');
                                $liveLi.find('.thumbsImage').removeClass('col-lg-5 col-sm-5 col-xs-5');
                                $liveLi.find('.videosDetails').removeClass('col-lg-7 col-sm-7 col-xs-7');
                                $liveLi.addClass('col-lg-2 col-md-4 col-sm-4 col-xs-6 fixPadding');
                                $('#liveVideos').slideDown();
                                return $liveLi;
                            }
                        </script>
                        <!-- For Live Videos End -->    
                        <?php
                        if ($obj->SortByName) { ?>   

                            <div class="clear clearfix">
                                <h3 class="galleryTitle">
                                    <i class="glyphicon glyphicon-list-alt"></i> <?php
                                    if(empty($_GET["sortByNameOrder"])){
                                        $_GET["sortByNameOrder"]="ASC";
                                    }
                                 if($obj->sortReverseable){   
                                   $info = createOrderInfo("sortByNameOrder","zyx","abc",$orderString);
                                    echo __("Sort by name (".$info[2].")")." (Page " . $_GET['page'] . ") <a href='".$info[0]."' >".$info[1]."</a>";
                                } else {
                                   echo __("Sort by name (abc)"); 
                                }
                                    ?>
                                </h3>
                                <div class="row">
                                    <?php
                                    $countCols = 0;
                                    unset($_POST['sort']);
                                    $_POST['sort']['title'] = $_GET['sortByNameOrder'];
                                    $_POST['current'] = $_GET['page'];
                                    $_POST['rowCount'] = $obj->SortByNameRowCount;
                                    $videos = Video::getAllVideos();
                                    foreach ($videos as $value) {
                                        $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
                                        $name = User::getNameIdentificationById($value['users_id']);
                                        // make a row each 6 cols
                                        if ($countCols % 6 === 0) {
                                            echo '</div><div class="row aligned-row ">';
                                        }
                                        $countCols++;
                                        ?>
                                        <div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 galleryVideo thumbsImage fixPadding">
                                            <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_category']; ?>/video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>" >
                                                <?php
                                                $images = Video::getImageFromFilename($value['filename'], $value['type']);
                                                $imgGif = $images->thumbsGif;
                                                $poster = $images->thumbsJpg;
                                                ?>
                                                <div class="aspectRatio16_9">
                                                    <img src="<?php echo $images->thumbsJpgSmall; ?>" data-src="<?php echo $poster; ?>" alt="<?php echo $value['title']; ?>" class="thumbsJPG img img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" id="thumbsJPG<?php echo $value['id']; ?>" />

                                                    <?php
                                                    if (!empty($imgGif)) {
                                                        ?>
                                                        <img src="<?php echo $global['webSiteRootURL']; ?>img/loading-gif.png" data-src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
                                                    <?php } ?>
                                                </div>
                                                <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                                            </a>
                                            <a class="h6" href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>">
                                                <h2><?php echo $value['title']; ?></h2>
                                            </a>

                                            <div class="text-muted galeryDetails">
                                                <div>
                                                    <?php
                                                    $value['tags'] = Video::getTags($value['id']);
                                                    foreach ($value['tags'] as $value2) {
                                                        if ($value2->label === __("Group")) {
                                                            ?>
                                                            <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <div>
                                                    <i class="fa fa-eye"></i>
                                                    <span itemprop="interactionCount">
                                                        <?php echo number_format($value['views_count'], 0); ?> <?php echo __("Views"); ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <i class="fa fa-clock-o"></i>
                                                    <?php
                                                    echo humanTiming(strtotime($value['videoCreation'])), " ", __('ago');
                                                    ?>
                                                </div>
                                                <div>
                                                    <i class="fa fa-user"></i>
                                                    <a class="text-muted" href="<?php echo $global['webSiteRootURL']; ?>channel/<?php echo $value['users_id']; ?>/">
                                                        <?php
                                                        echo $name;
                                                        ?>
                                                    </a>
                                                    <?php
                                                    if ((!empty($value['description'])) && ($obj->Description)) {
                                                        ?>
                                                        <button type="button" data-trigger="focus"  class="btn btn-xs" style="background-color: inherit; color: inherit;"  data-toggle="popover" data-placement="top" data-html="true" title="<?php echo $value['title']; ?>" data-content="<div><?php echo str_replace('"', '&quot;', nl2br(textToLink($value['description']))); ?></div>">Description</button>
                                                    <?php } ?>
                                                </div>
                                                <?php
                                                if(Video::canEdit($value['id'])){
                                                    ?>
                                                    <div>
                                                        <a href="<?php echo $global['webSiteRootURL']; ?>mvideos?video_id=<?php echo $value['id']; ?>" class="text-primary"><i class="fa fa-edit"></i> <?php echo __("Edit Video"); ?></a>
                                                    </div>
                                                <?php
                                                }
                                                ?>

                                            </div>
                                        </div>

                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="row">
                                    <ul class="pages">
                                    </ul>
                                </div>
                            </div>

                        <?php } if ($obj->DateAdded) { ?> 


                            <div class="clear clearfix">
                                <h3 class="galleryTitle">
                                    <i class="glyphicon glyphicon-sort-by-attributes"></i> <?php
                                    if(empty($_GET["dateAddedOrder"])){
                                        $_GET["dateAddedOrder"]="DESC";
                                    }
                                if($obj->sortReverseable){   
                                   $info = createOrderInfo("dateAddedOrder","newest","oldest",$orderString);
                                    echo __("Date added (".$info[2].")")." (Page " . $_GET['page'] . ") <a href='".$info[0]."' >".$info[1]."</a>";
                                } else {
                                   echo __("Date added (newest)"); 
                                }
                        
                        
                                    ?>
                                </h3>
                                <div class="row">
                                    <?php
                                    $countCols = 0;
                                    unset($_POST['sort']);
                                    $_POST['sort']['created'] = $_GET['dateAddedOrder'];
                                    $_POST['rowCount'] = $obj->DateAddedRowCount;
                                    $videos = Video::getAllVideos();
                                    foreach ($videos as $value) {
                                        $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
                                        $name = User::getNameIdentificationById($value['users_id']);
                                        // make a row each 6 cols
                                        if ($countCols % 6 === 0) {
                                            echo '</div><div class="row aligned-row ">';
                                        }
                                        $countCols++;
                                        ?>
                                        <div class="col-lg-2 col-sm-4 col-xs-6 galleryVideo thumbsImage fixPadding">
                                            <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_category']; ?>/video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>" >
                                                <?php
                                                $images = Video::getImageFromFilename($value['filename'], $value['type']);
                                                $imgGif = $images->thumbsGif;
                                                $poster = $images->thumbsJpg;
                                                ?>
                                                <div class="aspectRatio16_9">
                                                    <img src="<?php echo $images->thumbsJpgSmall; ?>" data-src="<?php echo $poster; ?>" alt="<?php echo $value['title']; ?>" class="thumbsJPG img img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>"  id="thumbsJPG<?php echo $value['id']; ?>"/>
                                                    <?php
                                                    if (!empty($imgGif)) {
                                                        ?>
                                                        <img src="<?php echo $global['webSiteRootURL']; ?>img/loading-gif.png" data-src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
                                                    <?php } ?>
                                                </div>
                                                <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                                            </a>
                                            <a class="h6" href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>">
                                                <h2><?php echo $value['title']; ?></h2>
                                            </a>  
                                            <div class="text-muted galeryDetails">
                                                <div>
                                                    <?php
                                                    $value['tags'] = Video::getTags($value['id']);
                                                    foreach ($value['tags'] as $value2) {
                                                        if ($value2->label === __("Group")) {
                                                            ?>
                                                            <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <div>
                                                    <i class="fa fa-eye"></i>
                                                    <span itemprop="interactionCount">
                                                        <?php echo number_format($value['views_count'], 0); ?> <?php echo __("Views"); ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <i class="fa fa-clock-o"></i>
                                                    <?php
                                                    echo humanTiming(strtotime($value['videoCreation'])), " ", __('ago');
                                                    ?>
                                                </div>
                                                <div>
                                                    <i class="fa fa-user"></i>
                                                    <a class="text-muted" href="<?php echo $global['webSiteRootURL']; ?>channel/<?php echo $value['users_id']; ?>/">
                                                        <?php
                                                        echo $name;
                                                        ?>
                                                    </a>
                                                    <?php
                                                    if ((!empty($value['description'])) && ($obj->Description)) {
                                                        ?>
                                                        <button type="button" class="btn btn-xs"   data-toggle="popover" data-trigger="focus" data-placement="top" data-html="true" style="background-color: inherit; color: inherit;" title="<?php echo $value['title']; ?>" data-content="<div><?php echo str_replace('"', '&quot;', nl2br(textToLink($value['description']))); ?></div>">Description</button>
                                                    <?php } ?>
                                                </div>
                                                <?php
                                                if(Video::canEdit($value['id'])){
                                                    ?>
                                                    <div>
                                                        <a href="<?php echo $global['webSiteRootURL']; ?>mvideos?video_id=<?php echo $value['id']; ?>" class="text-primary"><i class="fa fa-edit"></i> <?php echo __("Edit Video"); ?></a>
                                                    </div>
                                                <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="row">

                                    <ul class="pages">
                                    </ul>

                                </div>
                            </div>



                        <?php } if ($obj->MostWatched) { ?>
                            <div class="clear clearfix">
                                <h3 class="galleryTitle">
                                    <i class="glyphicon glyphicon-eye-open"></i> <?php

                                    if(empty($_GET['mostWatchedOrder'])){
                                        $_GET['mostWatchedOrder']="DESC";
                                    }
                                if($obj->sortReverseable){   
                                   $info = createOrderInfo("mostWatchedOrder","Most","Lessest",$orderString);
                                    echo __($info[2]." watched")." (Page " . $_GET['page'] . ") <a href='".$info[0]."' >".$info[1]."</a>";
                                } else {
                                   echo __("Most watched"); 
                                }
                                    ?>
                                </h3>
                                <div class="row">
                                    <?php
                                    $countCols = 0;
                                    unset($_POST['sort']);
                                    $_POST['sort']['views_count'] = $_GET['mostWatchedOrder'];
                                    $_POST['current'] = $_GET['page'];
                                    $_POST['rowCount'] = $obj->MostWatchedRowCount;
                                    $videos = Video::getAllVideos();
                                    foreach ($videos as $value) {
                                        $name = User::getNameIdentificationById($value['users_id']);
                                        // make a row each 6 cols
                                        if ($countCols % 6 === 0) {
                                            echo '</div><div class="row aligned-row ">';
                                        }
                                        $countCols++;
                                        ?>
                                        <div class="col-lg-2 col-sm-4 col-xs-6 galleryVideo thumbsImage fixPadding">
                                            <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_category']; ?>/video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>" >
                                                <?php
                                                $images = Video::getImageFromFilename($value['filename'], $value['type']);
                                                $imgGif = $images->thumbsGif;
                                                $poster = $images->thumbsJpg;
                                                ?>
                                                <div class="aspectRatio16_9">
                                                    <img src="<?php echo $images->thumbsJpgSmall; ?>" data-src="<?php echo $poster; ?>" alt="<?php echo $value['title']; ?>" class="thumbsJPG img img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" id="thumbsJPG<?php echo $value['id']; ?>" />

                                                    <?php
                                                    if (!empty($imgGif)) {
                                                        ?>
                                                        <img src="<?php echo $global['webSiteRootURL']; ?>img/loading-gif.png" data-src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
                                                    <?php } ?>
                                                </div>
                                                <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                                            </a>
                                            <a class="h6" href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>">
                                                <h2><?php echo $value['title']; ?></h2>
                                            </a>

                                            <div class="text-muted galeryDetails">
                                                <div>
                                                    <?php
                                                    $value['tags'] = Video::getTags($value['id']);
                                                    foreach ($value['tags'] as $value2) {
                                                        if ($value2->label === __("Group")) {
                                                            ?>
                                                            <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <div>
                                                    <i class="fa fa-eye"></i>
                                                    <span itemprop="interactionCount">
                                                        <?php echo number_format($value['views_count'], 0); ?> <?php echo __("Views"); ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <i class="fa fa-clock-o"></i>
                                                    <?php
                                                    echo humanTiming(strtotime($value['videoCreation'])), " ", __('ago');
                                                    ?>
                                                </div>
                                                <div>
                                                    <i class="fa fa-user"></i>
                                                    <a class="text-muted" href="<?php echo $global['webSiteRootURL']; ?>channel/<?php echo $value['users_id']; ?>/">
                                                        <?php
                                                        echo $name;
                                                        ?>
                                                    </a>
                                                    <?php
                                                    if ((!empty($value['description'])) && ($obj->Description)) {
                                                        ?>
                                                        <button type="button" class="btn btn-xs" data-trigger="focus" data-toggle="popover" data-placement="top" data-html="true" style="background-color: inherit; color: inherit;" title="<?php echo $value['title']; ?>" data-content="<div style='color: white;' ><?php echo str_replace('"', '&quot;', nl2br(textToLink($value['description']))); ?></div>">Description</button>
                                                    <?php } ?>
                                                </div>
                                                <?php
                                                if(Video::canEdit($value['id'])){
                                                    ?>
                                                    <div>
                                                        <a href="<?php echo $global['webSiteRootURL']; ?>mvideos?video_id=<?php echo $value['id']; ?>" class="text-primary"><i class="fa fa-edit"></i> <?php echo __("Edit Video"); ?></a>
                                                    </div>
                                                <?php
                                                }
                                                ?>

                                            </div>
                                        </div>

                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="row">
                                    <ul class="pages">
                                    </ul>
                                </div>
                            </div>
                        <?php } if ($obj->MostPopular) { ?>    
                            <div class="clear clearfix">
                                <h3 class="galleryTitle">
                                    <i class="glyphicon glyphicon-thumbs-up"></i> <?php
                                    if(empty($_GET['mostPopularOrder'])){
                                        $_GET['mostPopularOrder']="DESC";
                                    }
                                if($obj->sortReverseable){   
                                   $info = createOrderInfo("mostPopularOrder","Most","Lessest",$orderString);
                                    echo __($info[2]." popular")." (Page " . $_GET['page'] . ") <a href='".$info[0]."' >".$info[1]."</a>";
                                } else {
                                   echo __("Most popular"); 
                                }
                                    ?>
                                </h3>
                                <div class="row">
                                    <?php
                                    $countCols = 0;
                                    unset($_POST['sort']);
                                    $_POST['sort']['likes'] = $_GET['mostPopularOrder'];
                                    $_POST['current'] = $_GET['page'];
                                    $_POST['rowCount'] = $obj->MostPopularRowCount;
                                    $videos = Video::getAllVideos();
                                    foreach ($videos as $value) {
                                        $name = User::getNameIdentificationById($value['users_id']);
                                        // make a row each 6 cols
                                        if ($countCols % 6 === 0) {
                                            echo '</div><div class="row aligned-row ">';
                                        }
                                        $countCols++;
                                        ?>
                                        <div class="col-lg-2 col-sm-4 col-xs-6 galleryVideo thumbsImage fixPadding">
                                            <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_category']; ?>/video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>" >
                                                <?php
                                                $images = Video::getImageFromFilename($value['filename'], $value['type']);
                                                $imgGif = $images->thumbsGif;
                                                $poster = $images->thumbsJpg;
                                                ?>
                                                <div class="aspectRatio16_9">
                                                    <img src="<?php echo $images->thumbsJpgSmall; ?>" data-src="<?php echo $poster; ?>" alt="<?php echo $value['title']; ?>" class="thumbsJPG img img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" id="thumbsJPG<?php echo $value['id']; ?>" />

                                                    <?php
                                                    if (!empty($imgGif)) {
                                                        ?>
                                                        <img src="<?php echo $global['webSiteRootURL']; ?>img/loading-gif.png" data-src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
                                                    <?php } ?>
                                                </div>
                                                <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                                            </a>
                                            <a class="h6" href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>">
                                                <h2><?php echo $value['title']; ?></h2>
                                            </a>

                                            <div class="text-muted galeryDetails">
                                                <div>
                                                    <?php
                                                    $value['tags'] = Video::getTags($value['id']);
                                                    foreach ($value['tags'] as $value2) {
                                                        if ($value2->label === __("Group")) {
                                                            ?>
                                                            <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <div>
                                                    <i class="fa fa-eye"></i>
                                                    <span itemprop="interactionCount">
                                                        <?php echo number_format($value['views_count'], 0); ?> <?php echo __("Views"); ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <i class="fa fa-clock-o"></i>
                                                    <?php
                                                    echo humanTiming(strtotime($value['videoCreation'])), " ", __('ago');
                                                    ?>
                                                </div>
                                                <div>
                                                    <i class="fa fa-user"></i>
                                                    <a class="text-muted" href="<?php echo $global['webSiteRootURL']; ?>channel/<?php echo $value['users_id']; ?>/">
                                                        <?php
                                                        echo $name;
                                                        ?>
                                                    </a>
                                                    <?php
                                                    if ((!empty($value['description'])) && ($obj->Description)) {
                                                        ?>
                                                        <button type="button" class="btn btn-xs" data-trigger="focus" data-toggle="popover" data-placement="top" data-html="true" style="background-color: inherit; color: inherit;" title="<?php echo $value['title']; ?>" data-content="<div><?php echo str_replace('"', '&quot;', nl2br(textToLink($value['description']))); ?></div>">Description</button>
                                                    <?php } ?>
                                                </div>
                                                <?php
                                                if(Video::canEdit($value['id'])){
                                                    ?>
                                                    <div>
                                                        <a href="<?php echo $global['webSiteRootURL']; ?>mvideos?video_id=<?php echo $value['id']; ?>" class="text-primary"><i class="fa fa-edit"></i> <?php echo __("Edit Video"); ?></a>
                                                    </div>
                                                <?php
                                                }
                                                ?>

                                            </div>
                                        </div>

                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="row">
                                    <ul class="pages">
                                    </ul>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="alert alert-warning">
                        <span class="glyphicon glyphicon-facetime-video"></span> <strong><?php echo __("Warning"); ?>!</strong> <?php echo __("We have not found any videos or audios to show"); ?>.
                    </div>
                <?php } ?>
            </div>

            


        </div>
        <?php
        include 'include/footer.php';
        ?>

    </body>
</html>
<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>
