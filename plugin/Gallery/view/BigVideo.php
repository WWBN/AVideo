<?php if ($obj->BigVideo) { ?>
    <div class="clear clearfix">
        <div class="row thumbsImage">
            <div class="col-sm-6">
                <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $video['clean_category']; ?>/video/<?php echo $video['clean_title']; ?>" title="<?php echo $video['title']; ?>" style="">
                    <?php
                    $images = Video::getImageFromFilename($video['filename'], $video['type']);
                    $imgGif = $images->thumbsGif;
                    $poster = $images->poster;
                    ?>                                        
                    <div class="aspectRatio16_9">
                        <img src="<?php echo $images->thumbsJpgSmall; ?>" data-src="<?php echo $poster; ?>" alt="<?php echo $video['title']; ?>" class="thumbsJPG img img-responsive " style="height: auto; width: 100%;" id="thumbsJPG<?php echo $video['id']; ?>" />
                        <?php if (!empty($imgGif)) { ?>
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
                    <h4 class="mainAreaDescription" itemprop="description"><?php echo nl2br(textToLink($video['description'])); ?></h4>
                </div>
                <div class="text-muted galeryDetails">
                    <div>
                        <?php if (empty($_GET['catName'])) { ?>
                        <a class="label label-default" href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_category']; ?>/">
                            <?php
                            if (!empty($video['iconClass'])) {
                                ?>
                                <i class="<?php echo $video['iconClass']; ?>"></i> 
                                <?php
                            }
                            ?>
                            <?php echo $video['category']; ?>
                        </a>
                        <?php } ?>
                        <?php
                        $video['tags'] = Video::getTags($video['id']);
                        foreach ($video['tags'] as $value2) {
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
                        <span itemprop="interactionCount"><?php echo number_format($video['views_count'], 0); ?> <?php echo __("Views"); ?></span>
                    </div>
                    <div>
                        <i class="fa fa-clock-o"></i>
                        <?php echo humanTiming(strtotime($video['videoCreation'])), " ", __('ago'); ?>
                    </div>
                    <div>
                        <i class="fa fa-user"></i>
                        <a class="text-muted" href="<?php echo $global['webSiteRootURL']; ?>channel/<?php echo $video['users_id']; ?>/">
                            <?php echo $name; ?>
                        </a>
                    </div>
                    <?php if (Video::canEdit($video['id'])) { ?>
                        <div>
                            <a href="<?php echo $global['webSiteRootURL']; ?>mvideos?video_id=<?php echo $video['id']; ?>" class="text-primary"><i class="fa fa-edit"></i> <?php echo __("Edit Video"); ?></a>
                        </div>
                    <?php } ?>
                    <?php
                    if ($config->getAllow_download()) {
                        $ext = ".mp4";
                        if ($value['type'] == "audio") {
                            if (file_exists($global['systemRootPath'] . "videos/" . $value['filename'] . ".ogg")) {
                                $ext = ".ogg";
                            } else if (file_exists($global['systemRootPath'] . "videos/" . $value['filename'] . ".mp3")) {
                                $ext = ".mp3";
                            }
                        }
                        ?>
                        <div><a class="label label-default " role="button" href="<?php echo $global['webSiteRootURL'] . "videos/" . $value['filename'] . $ext; ?>" download="<?php echo $value['title'] . $ext; ?>"><?php echo __("Download"); ?></a></div>
                        <?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php
}
