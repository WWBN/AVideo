<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/category.php';

$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$isAudioOnly = false;
if (("http://" . $url === $global['webSiteRootURL'] . "audioOnly") || ("https://" . $url === $global['webSiteRootURL'] . "audioOnly")) {
    $isAudioOnly = true;
}
$isVideoOnly = false;
if (("http://" . $url === $global['webSiteRootURL'] . "videoOnly") || ("https://" . $url === $global['webSiteRootURL'] . "videoOnly")) {
    $isVideoOnly = true;
}
$o = YouPHPTubePlugin::getObjectData("YouPHPFlix");
$tmpSessionType;
if(!empty($_SESSION['type'])){
    $tmpSessionType = $_SESSION['type'];
}
unset($_SESSION['type']);
?>
<!DOCTYPE html>
<html>
    <head>
        <script>
            var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
            var pageDots = <?php echo empty($o->pageDots) ? "false" : "true"; ?>;
            var forceCatLinks = <?php if($o->ForceCategoryLinks){ echo "true"; } else { echo "false"; } ?>;
        </script>

        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/webui-popover/jquery.webui-popover.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $global['webSiteRootURL']; ?>plugin/YouPHPFlix/view/js/flickty/flickity.min.css" rel="stylesheet" type="text/css" />
        <?php include $global['systemRootPath'] . 'view/include/head.php'; ?>
        <title><?php echo $config->getWebSiteTitle(); ?></title>
    </head>
    <body>
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>

        <div class="container-fluid" id="mainContainer" style="display: none;"> 
        <?php
            $category = Category::getAllCategories();
            $currentCat;
            $currentCatType = array('type'=>99); // 99 because it will not match - only when found and be replaced.
            if(!empty($_GET['catName'])){
            foreach ($category as $cat) {
                if ($cat['clean_name'] == $_GET['catName']) {
                    $currentCat = $cat;
                    $currentCatType = Category::getCategoryType($cat['id']);
                }
            }
            }
        if (($o->SubCategorys) && (! empty($_GET['catName']))) {
            ?>
       <script>
    		setTimeout(function(){ document.getElementById('mainContainer').style="display: block;";document.getElementById('loading').style="display: none;" }, 1000);
	   </script>
		<div class="clear clearfix">
			<div class="row">
            <?php 
            if((($currentCat['parentId'] == "0") || ($currentCat['parentId'] == "-1"))) {
                if(!empty($_GET['catName'])){ ?>
                    <div>
                        <a class="btn btn-primary"  href="<?php echo $global['webSiteRootURL']; ?>"><?php echo __("Back to startpage"); ?> </a>
                    </div>
                <?php }
            }
            if (($currentCat['parentId'] != "0") && ($currentCat['parentId'] != "-1")) {
                $parentCat = Category::getCategory($currentCat['parentId']); ?>
                <div>
                    <a class="btn btn-primary" href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $parentCat['clean_name']; ?>"><?php echo __("Back to") . " " . $parentCat['name']; ?> </a>
                </div>
            <?php
            }
            $category = Category::getChildCategories($currentCat['id']);
            if(!empty($category)) { ?>
                <h2 style="margin-top: 30px;"><?php echo __("Sub-Category-Gallery"); ?>
                            <span class="badge"><?php echo count($category); ?></span>
				</h2>
            <?php
            }
            $countCols = 0;
            $originalCat = $_GET['catName'];
            unset($_POST['sort']);
            $_POST['sort']['title'] = "ASC";
            
            foreach ($category as $cat) {
                $_GET['catName'] = $cat['clean_name'];
                $description = str_ireplace(array("<br />","<br>","<br/>"),"\r\n", $cat['description']);
                unset($_POST['sort']);
                $_POST['sort']['title'] = "ASC";
                $_GET['limitOnceToOne'] = "1";
                $videos = Video::getAllVideos("viewableNotAd");
                // make a row each 6 cols
                if ($countCols % 6 === 0) {
                    echo '</div><div class="row aligned-row ">';
                }
                $countCols ++; 
  
                unset($_GET['catName']); ?>
                    <div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 galleryVideo thumbsImage fixPadding">
					   <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $cat['clean_name']; ?>" title="<?php $cat['name']; ?>"> 
                           <div class="aspectRatio16_9">
                <?php
                if (! empty($videos)) {
                    foreach ($videos as $value) {
                        //$name = User::getNameIdentificationById($value['users_id']); 
                        $images = Video::getImageFromFilename($value['filename'], $value['type']);
                        $poster = $images->thumbsJpg;
                        ?>
							<img src="<?php echo $poster; ?>" alt="" data-toggle="tooltip" title="<?php echo $description; ?>" class="thumbsJPG img img-responsive rotate<?php echo $value['rotation']; ?>" id="thumbsJPG<?php echo $value['id']; ?>" />
                            <?php if ((!empty($imgGif)) && (!$o->LiteGalleryNoGifs)) { ?>
                                    <img src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="" data-toggle="tooltip" title="<?php echo $description; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
                        <?php }
                        $sql = "SELECT COUNT(title) FROM videos WHERE status='a' AND categories_id = ?;";
                        $res = sqlDAL::readSql($sql,"i",array($value['categories_id']));
                        $videoCount = sqlDAL::fetchArray($res);
                        sqlDAL::close($res);
                        break;
                    }
                } else {
                    $poster = $global['webSiteRootURL'] . "view/img/notfound.jpg";
                    ?>
				    <img src="<?php echo $poster; ?>" alt="" data-toggle="tooltip" title="<?php echo $description; ?>" class="thumbsJPG img img-responsive" id="thumbsJPG<?php echo $cat['id']; ?>" />
                    <?php
                        $sql = "SELECT COUNT(title) FROM videos WHERE status='a' AND categories_id = ?;";
                        $res = sqlDAL::readSql($sql,"i",array($cat['id']));
                        $videoCount = sqlDAL::fetchArray($res);
                        sqlDAL::close($res);
                }
                ?>
                            </div>
			    <div class="videoInfo">
                            <?php if (!empty($videoCount)) { ?>
                                <span class="label label-default" style="top: 10px !important; position: absolute;"><i class="glyphicon glyphicon-cd"></i> <?php echo $videoCount[0]; ?></span>
                            <?php } ?>
                        </div>
						<div data-toggle="tooltip" title="<?php echo $description; ?>" class="tile__title" style="margin-left: 10%; width: 80% !important; bottom: 40% !important; opacity: 0.8 !important; text-align: center;">
                            <?php echo $cat['name']; ?>
                        </div>
					</a>
				</div>
                <?php
                    } // foreach $category 
                    unset($_POST['sort']);
                    $_GET['catName'] = $originalCat;
                ?>
            </div>
		</div>              
      <?php
        }
            if ($o->DateAdded) {
                
                
                $_POST['sort']['created'] = "DESC";
                $_POST['current'] = 1;
                $_POST['rowCount'] = 20;
                
                if (($currentCatType['type']=="2")||($isVideoOnly)||(($o->separateAudio) && ($isAudioOnly == false))){ 
                   $_SESSION['type'] = "video";
                } else if (($currentCatType['type']=="1")||($isAudioOnly)){
                    $_SESSION['type'] = "audio";
                } else {
                    unset($_SESSION['type']);
                }
                $videos = Video::getAllVideos("viewableNotAd");
                unset($_SESSION['type']);
		if(!empty($videos)){
                ?>
            <div class="row">
			<h2>
				<i class="glyphicon glyphicon-sort-by-attributes"></i> <?php
                echo __("Date added (newest)");
                ?>
                </h2>
			<div class="carousel">
                    <?php

                foreach ($videos as $value) {
                    $images = Video::getImageFromFilename($value['filename'], $value['type']);
                    $imgGif = $images->thumbsGif;
                    $img = $images->thumbsJpg;
                    $poster = $images->poster;
                    ?>
                <div class="carousel-cell tile ">
					<div class="slide thumbsImage" videos_id="<?php echo $value['id']; ?>" poster="<?php echo $poster; ?>" video="<?php echo $value['clean_title']; ?>" iframe="<?php echo $global['webSiteRootURL']; ?>videoEmbeded/<?php echo $value['clean_title']; ?>">
				        <div class="tile__media ">
				            <img alt="<?php echo $value['title']; ?>" class="tile__img thumbsJPG ing img-responsive carousel-cell-image" data-flickity-lazyload="<?php echo $img; ?>" />
                                    <?php if (! empty($imgGif)) { ?>
                                        <img style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="tile__img thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive img carousel-cell-image" data-flickity-lazyload="<?php echo $imgGif; ?>" />
                                    <?php } ?>
                        </div>
						<div class="tile__details">
							<div class="videoInfo">
								<span class="label label-default"><i class="fa fa-eye"></i> <?php echo $value['views_count']; ?></span>
                                <span class="label label-success"><i class="fa fa-thumbs-up"></i> <?php echo $value['likes']; ?></span>
                                <span class="label label-success"><a style="color: inherit;" class="tile__cat" cat="<?php echo $value['clean_category']; ?>" href="<?php echo $global['webSiteRootURL'] . "cat/" . $value['clean_category']; ?>"><i class="fa"></i> <?php echo $value['category']; ?></a></span>
                            <?php if ($config->getAllow_download()) { 
                                            $ext = ".mp4";
                                            if($value['type']=="audio"){
                                                    if(file_exists($global['systemRootPath']."videos/".$value['filename'].".ogg")){
                                                        $ext = ".ogg";
                                                    } else if(file_exists($global['systemRootPath']."videos/".$value['filename'].".mp3")){
                                                        $ext = ".mp3";
                                                    }
                                        } ?>
                            <span><a class="label label-default " href="<?php echo $global['webSiteRootURL'] . "videos/" . $value['filename'].$ext; ?>" download="<?php echo $value['title'] . $ext; ?>"><?php echo __("Download"); ?></a></span><?php } ?>
							</div>
							<div class="tile__title">
                                <?php echo $value['title']; ?>
                            </div>
							<div class="videoDescription">
                                <?php echo nl2br(textToLink($value['description'])); ?>
                            </div>
						</div>
					</div>
					<div class="arrow-down" style="display: none;"></div>
				</div>
                        <?php
                }
                ?>
                </div>
			<div class="poster list-group-item" style="display: none;">
				<div class="posterDetails ">
					<h2 class="infoTitle">Title</h2>
					<h4 class="infoDetails">Details</h4>
					<div class="infoText col-md-4 col-sm-12">Text</div>
					<div class="footerBtn" style="display: none;">
						<a class="btn btn-danger playBtn" href="#"><i class="fa fa-play"></i> <?php echo __("Play"); ?></a>
						<button class="btn btn-primary myList">
							<i class="fa fa-plus"></i> <?php echo __("My list"); ?></button>
					</div>

				</div>
			</div>
		</div>

            <?php
            } //}
            if (($o->separateAudio) && ($isAudioOnly == false) && ($isVideoOnly == false)) {    
                unset($_POST['sort']);
		$_POST['sort']['created'] = "DESC";
                $_SESSION['type'] = "audio";
                $videos = Video::getAllVideos("viewableNotAd");
                unset($_SESSION['type']);
                // check, if we are in a 
                $ok = true;
                if((!empty($_GET['catName']))){
                    if(!empty($videos)){
                $catType = Category::getCategoryType($videos[0]['categories_id']);
                if(($catType['type']!="1")&&($catType['type']!="0")){
                    // echo "hidden cause of video-type";
                    $ok = false;
                }} else {
                   $ok = false; 
                } }
                if($ok){
                ?>
            <div class="row">
			<h2>
				<i class="glyphicon glyphicon-music"></i> <?php
                echo __("Audio-Gallery by Date");
                ?>
                </h2>
			<div class="carousel">
                    <?php

                foreach ($videos as $value) {
                    $images = Video::getImageFromFilename($value['filename'], $value['type']);
                    $imgGif = $images->thumbsGif;
                    $img = $images->thumbsJpg;
                    $poster = $images->poster;
                    if(file_exists($global['systemRootPath']."videos/".$value['filename'].".jpg")){
                        $img = $global['webSiteRootURL']."videos/".$value['filename'].".jpg";
                    }
                    ?>
                    <div class="carousel-cell tile ">
					   <div class="slide thumbsImage" videos_id="<?php echo $value['id']; ?>" poster="<?php echo $poster; ?>" video="<?php echo $value['clean_title']; ?>" iframe="<?php echo $global['webSiteRootURL']; ?>videoEmbeded/<?php echo $value['clean_title']; ?>">
				        <div class="tile__media ">
							<img alt="<?php echo $value['title']; ?>" class="tile__img thumbsJPG ing img-responsive carousel-cell-image" data-flickity-lazyload="<?php echo $img; ?>" />
                            <?php if (! empty($imgGif)) { ?>
                                <img style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="tile__img thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive img carousel-cell-image" data-flickity-lazyload="<?php echo $imgGif; ?>" />
                            <?php } ?>
                        </div>
						<div class="tile__details">
							<div class="videoInfo">
								<span class="label label-default"><i class="fa fa-eye"></i> <?php echo $value['views_count']; ?></span>
                                <span class="label label-success"><i class="fa fa-thumbs-up"></i> <?php echo $value['likes']; ?></span>
                                <span class="label label-success"><a style="color: inherit;" class="tile__cat" cat="<?php echo $value['clean_category']; ?>" href="<?php echo $global['webSiteRootURL'] . "cat/" . $value['clean_category'];?>"><i class="fa"></i> <?php echo $value['category']; ?></a></span>
                            <?php if ($config->getAllow_download()) { 
                                            $ext = ".mp4";
                                            if($value['type']=="audio"){
                                                    if(file_exists($global['systemRootPath']."videos/".$value['filename'].".ogg")){
                                                        $ext = ".ogg";
                                                    } else if(file_exists($global['systemRootPath']."videos/".$value['filename'].".mp3")){
                                                        $ext = ".mp3";
                                                    }
                                        } ?>
                            <span><a class="label label-default " href="<?php echo $global['webSiteRootURL'] . "videos/" . $value['filename'].$ext; ?>" download="<?php echo $value['title'] . $ext; ?>"><?php echo __("Download"); ?></a></span><?php } ?>
							</div>
							<div class="tile__title">
                                        <?php echo $value['title'];?>
                            </div>
							<div class="videoDescription">
                                        <?php echo nl2br(textToLink($value['description'])); ?>
                            </div>
						</div>
					</div>
					<div class="arrow-down" style="display: none;"></div>
				</div>
                <?php } ?>
            </div>
			<div class="poster list-group-item" style="display: none;">
				<div class="posterDetails ">
					<h2 class="infoTitle">Title</h2>
					<h4 class="infoDetails">Details</h4>
					<div class="infoText col-md-4 col-sm-12">Text</div>
					<div class="footerBtn" style="display: none;">
						<a class="btn btn-danger playBtn" href="#"><i class="fa fa-play"></i> <?php echo __("Play"); ?></a>
						<button class="btn btn-primary myList"><i class="fa fa-plus"></i> <?php echo __("My list"); ?></button>
					</div>
				</div>
			</div>
		</div>
            
            <?php
                } 
            } //there
            if ($o->MostWatched) { ?>
            <div class="row">
			<h2>
				<i class="glyphicon glyphicon-eye-open"></i> <?php echo __("Most watched"); ?>
            		</h2>
			<div class="carousel">
                <?php
                unset($_POST['sort']);
                $_POST['sort']['views_count'] = "DESC";
                if (($currentCatType['type']=="2")||($isVideoOnly)||(($o->separateAudio) && ($isAudioOnly == false))){ 
                   $_SESSION['type'] = "video";
                } else if (($currentCatType['type']=="1")||($isAudioOnly)){
                    $_SESSION['type'] = "audio";
                } else {
                    unset($_SESSION['type']);
                }
                
                $videos = Video::getAllVideos("viewableNotAd");
                unset($_SESSION['type']);
                foreach ($videos as $value) {
                    $images = Video::getImageFromFilename($value['filename'], $value['type']);
                    $imgGif = $images->thumbsGif;
                    $img = $images->thumbsJpg;
                    $poster = $images->poster;
                    ?>
                    <div class="carousel-cell tile ">
                         <div class="slide thumbsImage" videos_id="<?php echo $value['id']; ?>" poster="<?php echo $poster; ?>" video="<?php echo $value['clean_title']; ?>" iframe="<?php echo $global['webSiteRootURL']; ?>videoEmbeded/<?php echo $value['clean_title']; ?>">
                                <div class="tile__media ">
                                    <img alt="<?php echo $value['title']; ?>" class="tile__img thumbsJPG ing img-responsive carousel-cell-image" data-flickity-lazyload="<?php echo $img; ?>" />
                                    <?php if (! empty($imgGif)) { ?>
                                        <img style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="tile__img thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive img carousel-cell-image" data-flickity-lazyload="<?php echo $imgGif; ?>" />
                                    <?php } ?>
                                </div>
                                <div class="tile__details">
                                    <div class="videoInfo">
                                        <span class="label label-default"><i class="fa fa-eye"></i> <?php echo $value['views_count']; ?></span>
                                        <span class="label label-success"><i class="fa fa-thumbs-up"></i> <?php echo $value['likes']; ?></span>
                                        <span class="label label-success"><a style="color: inherit;" class="tile__cat" cat="<?php echo $value['clean_category']; ?>" href="<?php echo $global['webSiteRootURL'] . "cat/" .$value['clean_category']; ?>"><i class="fa"></i> <?php echo $value['category']; ?></a></span>
                                    <?php if ($config->getAllow_download()) { 
                                            $ext = ".mp4";
                                            if($value['type']=="audio"){
                                                    if(file_exists($global['systemRootPath']."videos/".$value['filename'].".ogg")){
                                                        $ext = ".ogg";
                                                    } else if(file_exists($global['systemRootPath']."videos/".$value['filename'].".mp3")){
                                                        $ext = ".mp3";
                                                    }
                                        } ?>
                            <span><a class="label label-default " href="<?php echo $global['webSiteRootURL'] . "videos/" . $value['filename'].$ext; ?>" download="<?php echo $value['title'] . $ext; ?>"><?php echo __("Download"); ?></a></span><?php } ?>
                                    </div>
                                    <div class="tile__title">
                                        <?php echo $value['title']; ?>
                                    </div>
                                    <div class="videoDescription">
                                        <?php echo nl2br(textToLink($value['description'])); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="arrow-down" style="display: none;"></div>
                    </div>
                <?php } ?>
                </div>
			<div class="poster list-group-item" style="display: none;">
				<div class="posterDetails ">
					<h2 class="infoTitle">Title</h2>
					<h4 class="infoDetails">Details</h4>
					<div class="infoText col-md-4 col-sm-12">Text</div>
					<div class="footerBtn" style="display: none;">
						<a class="btn btn-danger playBtn" href="#"><i class="fa fa-play"></i> <?php
                echo __("Play");
                ?></a>
						<button class="btn btn-primary myList">
							<i class="fa fa-plus"></i> <?php
                echo __("My list");
                ?></button>
					</div>

				</div>
			</div>
		</div>
        <?php
            }
            
            if ($o->MostPopular) {
                ?>
            <div class="row">
			<h2>
				<i class="glyphicon glyphicon-thumbs-up"></i> <?php echo __("Most popular"); ?>
            </h2>
			<div class="carousel">
                <?php
                unset($_POST['sort']);
                $_POST['sort']['likes'] = "DESC";
                if (($currentCatType['type']=="2")||($isVideoOnly)||(($o->separateAudio) && ($isAudioOnly == false))){ 
                   $_SESSION['type'] = "video";
                } else if (($currentCatType['type']=="1")||($isAudioOnly)){
                    $_SESSION['type'] = "audio";
                } else {
                    unset($_SESSION['type']);
                }
                $videos = Video::getAllVideos("viewableNotAd");
                unset($_SESSION['type']);
                foreach ($videos as $value) {
                    $images = Video::getImageFromFilename($value['filename'], $value['type']);
                    $imgGif = $images->thumbsGif;
                    $img = $images->thumbsJpg;
                    $poster = $images->poster;
                    ?>
                <div class="carousel-cell tile ">
					<div class="slide thumbsImage" videos_id="<?php echo $value['id']; ?>" poster="<?php echo $poster; ?>" video="<?php echo $value['clean_title']; ?>" iframe="<?php echo $global['webSiteRootURL']; ?>videoEmbeded/<?php echo $value['clean_title']; ?>">
                        <div class="tile__media ">
                            <img alt="<?php echo $value['title']; ?>" class="tile__img thumbsJPG ing img-responsive carousel-cell-image" data-flickity-lazyload="<?php echo $img; ?>" />
                            <?php if (! empty($imgGif)) { ?>
                                <img style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="tile__img thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive img carousel-cell-image" data-flickity-lazyload="<?php echo $imgGif; ?>" />
                            <?php } ?>
                        </div>
						<div class="tile__details">
							<div class="videoInfo">
								<span class="label label-default"><i class="fa fa-eye"></i> <?php echo $value['views_count']; ?></span>
                                <span class="label label-success"><i class="fa fa-thumbs-up"></i> <?php echo $value['likes']; ?></span>
                                <span class="label label-success"><a style="color: inherit;" class="tile__cat" cat="<?php echo $value['clean_category']; ?>" href="<?php echo $global['webSiteRootURL'] . "cat/" . $value['clean_category']; ?>"><i class="fa"></i> <?php echo $value['category']; ?></a></span>
                            <?php if ($config->getAllow_download()) { 
                                            $ext = ".mp4";
                                            if($value['type']=="audio"){
                                                    if(file_exists($global['systemRootPath']."videos/".$value['filename'].".ogg")){
                                                        $ext = ".ogg";
                                                    } else if(file_exists($global['systemRootPath']."videos/".$value['filename'].".mp3")){
                                                        $ext = ".mp3";
                                                    }
                                        } ?>
                            <span><a class="label label-default " href="<?php echo $global['webSiteRootURL'] . "videos/" . $value['filename'].$ext; ?>" download="<?php echo $value['title'] . $ext; ?>"><?php echo __("Download"); ?></a></span><?php } ?>
							</div>
							<div class="tile__title">
                                <?php echo $value['title']; ?>
                            </div>
							<div class="videoDescription">
                                <?php echo nl2br(textToLink($value['description'])); ?>
                            </div>
						</div>
					</div>
					<div class="arrow-down" style="display: none;"></div>
				</div>
                        <?php
                }
                
                ?>
                </div>
			<div class="poster list-group-item" style="display: none;">
				<div class="posterDetails ">
					<h2 class="infoTitle">Title</h2>
					<h4 class="infoDetails">Details</h4>
					<div class="infoText col-md-4 col-sm-12">Text</div>
					<div class="footerBtn" style="display: none;">
						<a class="btn btn-danger playBtn" href="#"><i class="fa fa-play"></i> <?php
                echo __("Play");
                ?></a>
						<button class="btn btn-primary myList">
							<i class="fa fa-plus"></i> <?php
                echo __("My list");
                ?></button>
					</div>

				</div>
			</div>
		</div>


            <?php
            }
            
            unset($_POST['sort']);
            unset($_POST['current']);
            unset($_POST['rowCount']);
            if ($o->SortByName) {
                $_POST['sort']['title'] = "ASC";
            } else {
                $_POST['sort']['created'] = "DESC";
            }
            
            if ($o->DefaultDesign) {
                $catNameEmpty = false;
                if(empty($_GET['catName'])){
                    $catNameEmpty = true;
                }
                foreach ($category as $cat) {
                    $_GET['catName'] = $cat['clean_name'];
                    
                    // $_POST['rowCount'] = 18;
                    // $_POST['current'] = 1;
                    
                if (($currentCatType['type']=="2")||($isVideoOnly)||(($o->separateAudio) && ($isAudioOnly == false))){ 
                   $_SESSION['type'] = "video";
                } else if (($currentCatType['type']=="1")||($isAudioOnly)){
                    $_SESSION['type'] = "audio";
                } else {
                    unset($_SESSION['type']);
                }
                $videos = Video::getAllVideos("viewableNotAd");
                unset($_SESSION['type']);
                if (empty($videos)) {
                    continue;
                }
                    
                    ?>
                <div class="row">
			<a style="z-index: 9999;" href='<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $cat['clean_name']; ?>'>
				<h2 style="margin-top: 30px;">
					<i class="<?php echo $cat['iconClass']; ?>"></i><?php echo $cat['name']; ?>
                    <span class="badge"><?php echo count($videos); ?></span>
				</h2>
			</a>
			<div class="carousel">
                        <?php
                    foreach ($videos as $value) {
                        $images = Video::getImageFromFilename($value['filename'], $value['type']);
                        $imgGif = $images->thumbsGif;
                        $img = $images->thumbsJpg;
                        $poster = $images->poster;
                        ?>
            <div class="carousel-cell tile ">
					<div class="slide thumbsImage" videos_id="<?php echo $value['id']; ?>" poster="<?php echo $poster; ?>" cat="<?php echo $cat['clean_name']; ?>" video="<?php echo $value['clean_title']; ?>" iframe="<?php echo $global['webSiteRootURL']; ?>videoEmbeded/<?php echo $value['clean_title']; ?>">
				<div class="tile__media ">
							<img alt="<?php echo $value['title']; ?>" class="tile__img thumbsJPG ing img-responsive carousel-cell-image" data-flickity-lazyload="<?php echo $img; ?>" />
                        <?php if (! empty($imgGif)) { ?>
                            <img style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="tile__img thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive img carousel-cell-image" data-flickity-lazyload="<?php echo $imgGif; ?>" />
                        <?php } ?>
                </div>
						<div class="tile__details">
							<div class="videoInfo">
								<span class="label label-default"><i class="fa fa-eye"></i> <?php echo $value['views_count']; ?></span> 
                                <span class="label label-success"><i class="fa fa-thumbs-up"></i> <?php echo $value['likes']; ?></span>
                                <span class="label label-success"><a style="color: inherit;" class="tile__cat" cat="<?php echo $value['clean_category']; ?>" href="<?php echo $global['webSiteRootURL'] . "cat/" . $value['clean_category']; ?>"><i class="fa"></i> <?php echo $value['category']; ?></a></span>
                                                            <?php if ($config->getAllow_download()) { 
                                            $ext = ".mp4";
                                            if($value['type']=="audio"){
                                                    if(file_exists($global['systemRootPath']."videos/".$value['filename'].".ogg")){
                                                        $ext = ".ogg";
                                                    } else if(file_exists($global['systemRootPath']."videos/".$value['filename'].".mp3")){
                                                        $ext = ".mp3";
                                                    }
                                        } ?>
                            <span><a class="label label-default " href="<?php echo $global['webSiteRootURL'] . "videos/" . $value['filename'].$ext; ?>" download="<?php echo $value['title'] . $ext; ?>"><?php echo __("Download"); ?></a></span><?php } ?>
							</div>
							<div class="tile__title">
                                        <?php echo $value['title']; ?>
                            </div>
							<div class="videoDescription">
                                <?php echo nl2br(textToLink($value['description'])); ?>
                            </div>
						</div>
					</div>
					<div class="arrow-down" style="display: none;"></div>
				</div>
                            <?php } ?>
            </div>
			<div class="poster list-group-item" style="display: none;">
				<div class="posterDetails ">
					<h2 class="infoTitle">Title</h2>
					<h4 class="infoDetails">Details</h4>
					<div class="infoText col-md-4 col-sm-12">Text</div>
					<div class="footerBtn" style="display: none;">
						<a class="btn btn-danger playBtn" href="#"><i class="fa fa-play"></i> <?php
                    echo __("Play");
                    ?></a>
						<button class="btn btn-primary myList">
							<i class="fa fa-plus"></i> <?php
                    echo __("My list");
                    ?></button>
					</div>

				</div>
			</div>
		</div>
                <?php
                }
                if($catNameEmpty){
                    unset($_GET['catName']);
                }
            }
        }
        
        if (($o->LiteGallery) && (empty($_GET['catName']))) {
            
            // $catCount = $global['mysqli']->query("SELECT COUNT(name) FROM categories WHERE parentId = 0 OR parentId = -1;");
            
            $_GET['parentsOnly'] = "1";
            $liteGalleryCategory = Category::getAllCategories();
            ?>
        <script>
    		  setTimeout(function(){ document.getElementById('mainContainer').style="display: block;";document.getElementById('loading').style="display: none;" }, 1000);
	    </script>
		<div class="clear clearfix">
			<div class="row">
				<h2 style="margin-top: 30px;">
                    <?php echo __("Category-Gallery"); ?>
                    <span class="badge"><?php echo Category::getTotalCategories()?></span>
				</h2>
        <?php
            $countCols = 0;
            
            
            $audioReplacePicture;
            // $_POST['rowCount'] = 12;
            
            foreach ($liteGalleryCategory as $cat) {
                unset($_POST['sort']);
                $catType = Category::getCategoryType($cat['id']);
                $description = str_ireplace(array("<br />","<br>","<br/>"),"\r\n", $cat['description']);
                // -1 is only a personal workaround
                if (($cat['parentId'] == "0") || ($cat['parentId'] == "-1")) {
                    $_GET['catName'] = $cat['clean_name'];
                    $_GET['limitOnceToOne'] = "1";
                    $_POST['sort']['title'] = "ASC";
                    $_SESSION['type'] = "video";
                    $videos = Video::getAllVideos("viewableNotAd");
                    $i = 0;
                    
                    // when this cat has no video for preview..
                    if (empty($videos)) {
                        // First: search in subcats for videos for preview. Makes more sense since audio has none
                        // if, after 10 tries nothing is media is found, it gives up.
                        
                        unset($_POST['sort']);
                        $subcats = Category::getChildCategories($cat['id']);
                        foreach ($subcats as $sCat) {
                                unset($_POST['sort']);
                                $intsubcats = Category::getChildCategories($sCat['id']);
                                foreach ($intsubcats as $intSubCat) {
                                    $i = $i + 1;
                                    $_POST['sort']['title'] = "ASC";
                                    $_GET['catName'] = $intSubCat['clean_name'];
                                    $_GET['limitOnceToOne'] = "1";
                                    $_SESSION['type'] = "video";
                                    $videos = Video::getAllVideos("viewableNotAd");
                                    if ((! empty($videos)) || ($i > 10)) {
                                        break;
                                    }
                                }
                                if(! empty($videos)){
                                    break;
                                }
                                
                        }
                        
                        $i = 0;
                        
                        // if still empty, take a audio for the same
                        // this can be done much easier, but it's a good place to make a diffrent between pure audio-cat's and video/mixed and separate them (collect in array), other foreach after = audio-cat-gallery
			if(empty($videos)){	
			$catType = Category::getCategoryType($cat['id']);
			//echo "blablabla";
			//var_dump($catType);
                            if(($catType['type']=="2")||($catType['type']=="0")||($catType['type']=="-1")){
                                $audioReplacePicture = "view/img/notfound.jpg"; 
                            } else {
                                $audioReplacePicture = "view/img/audio_wave.jpg";
                            }
                    } }
                    if(!empty($audioReplacePicture)){
                        if ($o->LiteGalleryMaxTooltipChars > 4) {
                            if (strlen($description) > $o->LiteGalleryMaxTooltipChars) {
                                $description = substr($description, 0, $o->LiteGalleryMaxTooltipChars - 3) . "...";
                            } 
                        } else {
                            $description = "";
                        }
                        if ($countCols % 6 === 0) {
                            echo '</div><div class="row aligned-row ">';
                        }
                        $countCols ++;
                        ?>
                        <div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 galleryVideo thumbsImage fixPadding">
					       <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $cat['clean_name']; ?>" title="<?php $cat['name']; ?>">
                                <div class="aspectRatio16_9">
                                    <img src="<?php echo $global['webSiteRootURL'].$audioReplacePicture; ?>" alt="" data-toggle="tooltip" title="<?php echo $description; ?>" class="thumbsJPG img img-responsive" />
                                </div>
                            <div class="videoInfo">
                            <?php if (!empty($videoCount)) { ?>
                                <span class="label label-default" style="top: 10px !important; position: absolute;"> <?php
                                    if($catType){
                                        if(($catType['type']==0)||($catType['type']==2)){
                                            echo '<i class="glyphicon glyphicon-cd"></i>';
                                        } else {
                                           echo '<i class="glyphicon glyphicon-music"></i>'; 
                                        }
                                    }
                                    echo $videoCount[0]; ?>
                                </span>
                                    <?php } ?>
                            </div>
                            <div data-toggle="tooltip" title="<?php echo $description; ?>" class="tile__title" style="margin-left: 10%; width: 80% !important; bottom: 40% !important; opacity: 0.8 !important; text-align: center;">
                                <?php echo $cat['name']; ?>
                            </div>
                        </a>
                    </div>
                    <?php
			         unset($audioReplacePicture);
                    } else {
                    
                    foreach ($videos as $value) {
                       // $name = User::getNameIdentificationById($value['users_id']);
                        // make a row each 6 cols
                        if ($countCols % 6 === 0) {
                            echo '</div><div class="row aligned-row ">';
                        }
                        $countCols ++;
                        ?>
                <div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 galleryVideo thumbsImage fixPadding">
					<a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $cat['clean_name']; ?>" title="<?php $cat['name']; ?>">
                    <?php
                        $images = Video::getImageFromFilename($value['filename'], $value['type']);
                        if (! $o->LiteGalleryNoGifs) {
                            $imgGif = $images->thumbsGif;
                        }
                        
                        $poster = $images->thumbsJpg;
                        if ($o->LiteGalleryMaxTooltipChars > 4) {
                            if (strlen($description) > $o->LiteGalleryMaxTooltipChars) {
                                $description = substr($description, 0, $o->LiteGalleryMaxTooltipChars - 3) . "...";
                            }
                        } else {
                            $description = "";
                        }
                    ?>
                    <div class="aspectRatio16_9">
				        <img src="<?php echo $poster; ?>" alt="" data-toggle="tooltip" title="<?php echo $description; ?>" class="thumbsJPG img img-responsive rotate<?php echo $value['rotation']; ?>" id="thumbsJPG<?php echo $value['id']; ?>" />
                        <?php
                            if ((!empty($imgGif)) && (!$o->LiteGalleryNoGifs)) {
                        ?>
                                <img src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="" data-toggle="tooltip" title="<?php echo $description; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
                        <?php
                            }
                            $sql = "SELECT COUNT(title) FROM videos WHERE status='a' AND categories_id = ?;";
                            $res = sqlDAL::readSql($sql,"i",array($value['categories_id']));
                            $videoCount = sqlDAL::fetchArray($res);
                            sqlDAL::close($res);
                        ?>
                    </div>
						<div class="videoInfo">
                        <?php
                            if (!empty($videoCount)) {
                                ?>
                            <span class="label label-default" style="top: 10px !important; position: absolute;"> <?php
                                if($catType){
                                    if(($catType['type']==0)||($catType['type']==2)){
                                        echo '<i class="glyphicon glyphicon-cd"></i>';
                                    } else {
                                       echo '<i class="glyphicon glyphicon-music"></i>'; 
                                    }
                                }
                                echo $videoCount[0];
                                ?>
                            </span>
                                <?php
                            }
                        ?>
                        </div>
						<div data-toggle="tooltip" title="<?php echo $description; ?>" class="tile__title" style="margin-left: 10%; width: 80% !important; bottom: 40% !important; opacity: 0.8 !important; text-align: center;">
                            <?php echo $cat['name']; ?>
                        </div>
					</a>
				</div>        
                    <?php
                        break;
                    }
                    }
                }
            }
            
            ?>
                </div>
		</div>                
        <?php } if ($o->LiteDesign) { ?>
            <div class="row">
                <h2 style="margin-top: 30px;"><?php echo __("Categories"); ?> <span class="badge"><?php echo count($category); ?></span></h2>
                <div class="carousel">
                    <?php
                    foreach ($category as $cat) {
                        $_GET['catName'] = $cat['clean_name'];
                        $_GET['limitOnceToOne'] = "1";
                        $videos = Video::getAllVideos("viewableNotAd");
                        if (empty($videos)) {
                            continue;
                        }
                        foreach ($videos as $value) {
                            $images = Video::getImageFromFilename($value['filename'], $value['type']);
                            if (! $o->LiteDesignNoGifs) {
                                $imgGif = $images->thumbsGif;
                            }
                            $img = $images->thumbsJpg;
                            $poster = $images->poster;
                    ?>
                    <div class="carousel-cell tile ">
                        <a href="<?php echo $global['webSiteRootURL'] . "cat/" . $cat['clean_name']; ?>">
                            <div class="slide" videos_id="<?php echo $value['id']; ?>" poster="<?php echo $poster; ?>" cat="<?php echo $cat['clean_name']; ?>" video="<?php echo $value['clean_title']; ?>" iframe="<?php echo $global['webSiteRootURL']; ?>videoEmbeded/<?php echo $value['clean_title']; ?>">
                                <div class="tile__media ">
                                    <img alt="<?php echo $value['title']; ?>" class="tile__img thumbsJPG ing img-responsive carousel-cell-image" data-flickity-lazyload="<?php echo $img; ?>" />
                                    <?php if ((! empty($imgGif)) && (! $o->LiteDesignNoGifs)) { ?>
                                    <img style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="tile__img thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive img carousel-cell-image" data-flickity-lazyload="<?php echo $imgGif; ?>" />
                                    <?php
                                    }
                                    $sql = "SELECT COUNT(title) FROM videos WHERE status='a' AND categories_id = ?;";
                                    $res = sqlDAL::readSql($sql,"i",array($value['categories_id']));
                                    $videoCount = sqlDAL::fetchArray($res);
                                    sqlDAL::close($res); ?>
                                </div>
                                <div class="">
                                    <div class="videoInfo">
                                        <?php if (!empty($videoCount)) { ?>
                                        <span class="label label-default" style="top: 10px !important; position: absolute;">
                                            <i class="glyphicon glyphicon-cd"></i> <?php
                                            echo $videoCount[0]; ?>
                                        </span>
                                        <?php } ?>
                                    </div>
                                    <div class="tile__title" style="bottom: 40% !important; opacity: 0.8 !important; text-align: center;">
                                        <?php echo $cat['name']; ?>
                                    </div>
                                    <div class="videoDescription">
                                        <?php echo nl2br(textToLink($value['description'])); ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <div class="arrow-down" style="display: none;"></div>
                    </div>
                    <?php break; } } ?> 
                </div>
            </div> 
        <?php } //end of lite-design ?>
        </div>
	<div id="loading" class="loader"
		style="width: 30vh; height: 30vh; position: absolute; left: 50%; top: 50%; margin-left: -15vh; margin-top: -15vh;"></div>
        <div class="webui-popover-content" id="popover">
            <?php if (User::isLogged()) { ?>
            <form role="form">
                <div class="form-group">
                    <input class="form-control" id="searchinput" type="search" placeholder="<?php echo __("Search..."); ?>" />
                </div>
                <div id="searchlist" class="list-group"></div>
            </form>
            <div>
                <hr>
                <div class="form-group">
                    <input id="playListName" class="form-control" placeholder="<?php echo __("Create a New Play List"); ?>">
                </div>
                <div class="form-group">
                    <?php echo __("Make it public"); ?>
                    <div class="material-switch pull-right">
                        <input id="publicPlayList" name="publicPlayList" type="checkbox" checked="checked" />
                        <label for="publicPlayList" class="label-success"></label>
                    </div>
                </div>
                <div class="form-group">
                    <button class="btn btn-success btn-block" id="addPlayList"><?php echo __("Create a New Play List"); ?></button>
                </div>
            </div>
            <?php } else { ?>
            <h5><?php echo __("Want to watch this again later?"); ?></h5>
		      <?php echo __("Sign in to add this video to a playlist."); ?>
            <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-primary">
                <span class="glyphicon glyphicon-log-in"></span>
                <?php echo __("Login"); ?>
            </a>
            <?php } ?>
        </div>        
        <?php include $global['systemRootPath'] . 'view/include/footer.php';

        if(!empty($tmpSessionType)){
            $_SESSION['type'] = $tmpSessionType;
        } else {
	   unset($_SESSION['type']);
	}
    $jsFiles = array("view/js/bootstrap-list-filter/bootstrap-list-filter.min.js","plugin/YouPHPFlix/view/js/flickty/flickity.pkgd.min.js","view/js/webui-popover/jquery.webui-popover.min.js","plugin/YouPHPFlix/view/js/script.js");
    $jsURL =  combineFiles($jsFiles, "js");
?>
<script src="<?php echo $jsURL; ?>" type="text/javascript"></script>
    </body>
</html>
