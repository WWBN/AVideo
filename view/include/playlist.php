<?php
require_once $global['systemRootPath'] . 'objects/playlist.php';
$playlist = new PlayList($playlist_id);
$playlistVideos = PlayList::getVideosFromPlaylist($playlist_id);
?>
<div class="playlist-nav row">
    <nav class="navbar navbar-inverse">
        <ul class="nav navbar-nav">
            <li class="navbar-header">
                <a>
                    <h3 class="nopadding">
                        <?php
                        echo $playlist->getName();
                        ?>
                    </h3>
                    <small>
                        <?php
                        echo ($playlist_index+1), "/", count($playlistVideos), " ", __("Videos");
                        ?>
                    </small>
                </a>
            </li>
        </ul>
    </nav>
    <nav class="navbar navbar-inverse playlistList">
        <ul class="nav navbar-nav">
            <?php
            $count = 0;
            foreach ($playlistVideos as $value) {
                $class = "";
                $indicator = $count+1;
                if ($count==$playlist_index) {
                    $class .= " active";
                    $indicator = '<span class="fa fa-play text-danger"></span>';
                }
            ?>
            <li class="<?php echo $class; ?>">
                    <a href="<?php echo $global['webSiteRootURL']; ?>playlist/<?php echo $playlist_id; ?>/<?php echo $count; ?>" title="<?php echo $value['title']; ?>" class="videoLink row">
                        <div class="col-md-1 col-sm-1 col-xs-1">
                            <?php echo $indicator; ?>
                        </div>
                        <div class="col-md-3 col-sm-3 col-xs-3 nopadding">
                            <?php
                            if ($value['type'] !== "audio") {
                                $img = "{$global['webSiteRootURL']}videos/{$value['filename']}.jpg";
                                $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
                            } else {
                                $img = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
                                $img_portrait = "";
                            }
                            ?>
                            <img src="<?php echo $img; ?>" alt="<?php echo $value['title']; ?>" class="img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" itemprop="thumbnail" />

                            <meta itemprop="thumbnailUrl" content="<?php echo $img; ?>" />
                            <meta itemprop="contentURL" content="<?php echo $global['webSiteRootURL'], $catLink, "video/", $value['clean_title']; ?>" />
                            <meta itemprop="embedURL" content="<?php echo $global['webSiteRootURL'], "videoEmbeded/", $value['clean_title']; ?>" />
                            <meta itemprop="uploadDate" content="<?php echo $value['created']; ?>" />

                            <time class="duration" itemprop="duration" datetime="<?php echo Video::getItemPropDuration($value['duration']); ?>"><?php echo Video::getCleanDuration($value['duration']); ?></time>
                        </div>
                        <div class="col-md-8 col-sm-8 col-xs-8 videosDetails">
                            <div class="text-uppercase row"><strong itemprop="name" class="title"><?php echo $value['title']; ?></strong></div>
                            <div class="details row" itemprop="description">
                                <div>
                                    <span class="<?php echo $value['iconClass']; ?>"></span>
                                </div>
                                <div>
                                    <strong class=""><?php echo number_format($value['views_count'], 0); ?></strong> <?php echo __("Views"); ?>
                                </div>

                            </div>
                        </div>
                    </a>
                </li>
                <?php
                $count++;
            }
            ?>
        </ul>
    </nav>
</div>