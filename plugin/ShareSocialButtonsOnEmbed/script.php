<div id="share" style="z-index: 280; position: absolute; top: 10px; right: 10px; display: none;" class="text-center">
    <button type="button" class="btn btn-light" style="width: 30px;
            height: 30px;
            text-align: center;
            padding: 6px 0;
            font-size: 12px;
            line-height: 1.428571429;
            border-radius: 50%;"  title="<?php echo __("Share"); ?>"><i class="fa fa-share"></i></button>
</div>
<div id="modalSocial" style="background-color: rgba(0,0,0,0.8); width:100%;
     height:     100%; 
     z-index:    290;
     top:        0; 
     left:       0; 
     position:   fixed;display: none;">
    <div id="close" style="z-index: 280; position: absolute; top: 10px; right: 10px;" class="text-center">
        <button type="button" class="btn btn-light" style="width: 30px;
                height: 30px;
                text-align: center;
                padding: 6px 0;
                font-size: 12px;
                line-height: 1.428571429;
                border-radius: 50%;"   title="<?php echo __("Close"); ?>"><i class="far fa-window-close"></i></button>
    </div>
    <h2 style="z-index: 290; color: white;" class="text-center"><?php echo __("Share"); ?></h2>
    <div id="social" style="z-index: 300; position: absolute; top: 10px; left: 50%; margin-left: -200px; width: 400px; " class="text-center">
        <?php
        $url = urlencode($global['webSiteRootURL'] . "video/" . $video['clean_title']);
        $title = urlencode($video['title']);
        ?>
        <div class="row">
        <a class="" target="_blank" aria-label="Share link" href="<?php echo urldecode($url); ?>" title="Share link" style="display: block;
           height: 28px;
           margin-top: 18px;
           text-overflow: ellipsis;
           font-size: 120%;
           color: white;
           font-weight: 500;
           letter-spacing: 1px;
           white-space: nowrap;
           overflow: hidden;
           text-decoration: none;
           outline: 0;"><?php echo urldecode($url); ?></a>   
           </div>
           <div class="row">
           <?php
           include './include/social.php';
           ?>
        </div>
        
        <div class="row" style="margin-top: 10px;">
        <button class="btn btn-light btn-sm copyTooltip" id="cpLink"><i class="fa fa-link" aria-hidden="true"></i> Copy Link</button>
        <button class="btn btn-light btn-sm copyTooltip" id="cpEmbed"><i class="fa fa-code" aria-hidden="true"></i> Copy Embed</button>
        </div>
    </div>

</div>
<script>
    var waitSocial = 0;
    var timeout;
    function setTooltip(element, message) {
        $(element).attr('data-original-title', message)
                .tooltip('show');
    }
    $(document).ready(function () {
        $('.copyTooltip').tooltip({
            trigger: 'click',
            placement: 'bottom'
        });
        if (typeof player == 'undefined') {
            player = videojs('mainVideo'<?php echo PlayerSkins::getDataSetup(); ?>);
        }
        $('#cpLink, #cpEmbed').mouseleave(function (event) {
             $(this).tooltip('hide');
        });
        $('#cpLink').click(function (event) {
            copyToClipboard('<?php echo urldecode($url); ?>');
            setTooltip(this, 'Copied!');
        });
        $('#cpEmbed').click(function (event) {
            copyToClipboard('<?php
           if ($video['type'] == 'video' || $video['type'] == 'embed') {
               $code = '<iframe width="640" height="480" style="max-width: 100%;max-height: 100%;" src="' . $global['webSiteRootURL'] . 'videoEmbeded/' . $video['clean_title'] . '" frameborder="0" allowfullscreen="allowfullscreen" ></iframe>';
           } else {
               $code = '<iframe width="350" height="40" style="max-width: 100%;max-height: 100%;" src="' . $global['webSiteRootURL'] . 'videoEmbeded/' . $video['clean_title'] . '" frameborder="0" allowfullscreen="allowfullscreen" ></iframe>';
           }
           echo ($code);
           ?>');
            setTooltip(this, 'Copied!');
        });
        $('#share').click(function (event) {
            waitSocial = 1;
            event.stopPropagation();
            var top = ($('.embed-responsive').outerHeight() / 2) - 55;
            console.log(top);
            $('#social').css({"top": top + "px"});
            $('#modalSocial').fadeIn();

        });
        $('#close').click(function (event) {
            waitShare = 0;
            $('#modalSocial').fadeOut();
        });
        $('#modalSocial').mouseleave(function (event) {
            waitSocial = 0;
        });
        $('.embed-responsive, #modalSocial').mouseleave(function () {
            setTimeout(function () {
                if (!waitSocial) {
                    $('#modalSocial').fadeOut();
                }
            }, 1000);
        });


        var waitShare = 0;

        $('#share').mouseenter(function (event) {
            waitShare = 1;
            event.stopPropagation();
            $('#share').fadeIn();

        });
        $('.embed-responsive').mouseenter(function (event) {
            event.stopPropagation();
            $('#share').fadeIn();

        });
        $('#share').mouseleave(function (event) {
            setTimeout(function () {
                waitShare = 0;
            }, 500);
        });
        $('.embed-responsive').mouseleave(function () {
            console.log("leave responsive: " + waitShare);
            setTimeout(function () {
                if (!waitShare) {
                    $('#share').fadeOut();
                }
            }, 1000);
        });
    });
</script>