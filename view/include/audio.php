<div class="row main-video" style="padding: 10px;">
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8 ">
        <audio controls class="center-block video-js vjs-default-skin "  id="mainAudio" autoplay data-setup='{}'
               poster="<?php echo $global['webSiteRootURL']; ?>img/recorder.gif">
            <?php if(file_exists($global['systemRootPath']."videos/".$video['filename'].".ogg")){ ?>
            <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.ogg" type="audio/ogg" />
            <a href="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.ogg">horse</a>
            <?php } else { ?>
            <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.mp3" type="audio/mpeg" /> 
            <a href="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.mp3">horse</a>
            <?php } ?>
        </audio>
    </div>

    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->