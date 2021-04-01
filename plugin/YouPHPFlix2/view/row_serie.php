<div class="poster rowSerie" id="poster<?php echo $uid; ?>" poster="<?php echo $poster; ?>"
     style="
     display: none;
     background-image: url(<?php echo $global['webSiteRootURL']; ?>plugin/YouPHPFlix2/view/img/loading.gif);
     -webkit-background-size: cover;
     -moz-background-size: cover;
     -o-background-size: cover;
     background-size: cover;
     ">
    <div class="topicRow">
        <h2 class="infoTitle">
            <?php
            $link = PlayLists::getLink($value['serie_playlists_id']);
            $linkEmbed = PlayLists::getLink($value['serie_playlists_id'], true);
            $canWatchPlayButton = "";
            if (User::canWatchVideoWithAds($value['id'])) {
                $canWatchPlayButton = "canWatchPlayButton";
            }
            $value['title'] = "<a href='{$link}' embed='{$linkEmbed}' class='{$canWatchPlayButton}'>{$value['title']}</a>";
            echo $value['title'];
            ?>
        </h2>
        <div id="ajaxLoad-<?php echo $uid; ?>" class="flickity-area col-sm-12"><?php echo __('Loading...'); ?></div>
    </div>
</div>