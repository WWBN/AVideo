<?php
$caUid = 'ChannelArt_' . uniqid();
?>
<style>
    #custom-handle {
        width: 3em;
        height: 1.6em;
        top: 50%;
        margin-top: -.8em;
        text-align: center;
        line-height: 1.6em;
    }
    .gudeHorizontal, .gudeVertical{
        background-color: #FF0000AA;
        border-left: solid 1px #00FF00AA;
        border-right: solid 1px #FF0000AA;
        width: 1px;
        height: 100%;
        position: absolute;
    }

    .gudeVertical{
        width: 100%;
        height: 1px;
        border-top: solid 1px #00FF00AA;
        border-bottom: solid 1px #FF0000AA;
    }
</style>
<div class="form-group" id="<?php echo $caUid; ?>">   
    <a href="<?php echo getURL('view/img/sampleGuide.png'); ?>" target="_blank" class="btn btn-default pull-right"><i class="fas fa-question-circle"></i> <?php echo __('Channel Art Help'); ?></a>
    <?php
    $croppie = getCroppie(__('Select new Channel Art'), 'channelArtUpload', $finalWidth, $finalHeight, $screenWidth, 100, $screenHeight, false);
    echo $croppie['html'];
    ?>
</div>  
<button class="btn btn-success btn-block" type="button" onclick="<?php echo $croppie['getCroppieFunction']; ?>"><i class="fas fa-save"></i> <?php echo __('Save Channel Art'); ?></button>


<script>
    widthTV = 2550;
    widthDesktop = 2550;
    widthTablet = 1855;
    widthDesktop = 1546;
    heightTV = 1440;
    heightDevices = 423;
    factorW = <?php echo $factorW; ?>;

    $(document).ready(function () {


<?php
echo $croppie['restartCroppie'] . "('" . getURL($channelArtRelativePath) . "');";
?>
        addGuides<?php echo $caUid; ?>();
    });
    function channelArtUpload(image) {

        modal.showPleaseWait();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL'] . "objects/uploadChannelArt.json.php"; ?>',
            data: {
                image: image
            },
            type: 'post',
            success: function (response) {
                avideoResponse(response);
                modal.hidePleaseWait();
            }
        });
    }

    function addGuides<?php echo $caUid; ?>() {
        if ($('<?php echo '#' . $caUid; ?> .cr-viewport').length) {
            addGuide<?php echo $caUid; ?>('TV', 1, true);
            addGuide<?php echo $caUid; ?>('TV', 2, true);

            addGuide<?php echo $caUid; ?>('Tablet', 1, true);
            addGuide<?php echo $caUid; ?>('Tablet', 2, true);

            addGuide<?php echo $caUid; ?>('Desktop', 1, true);
            addGuide<?php echo $caUid; ?>('Desktop', 2, true);

            addGuide<?php echo $caUid; ?>('any', 1, false);
            addGuide<?php echo $caUid; ?>('any', 2, false);

        } else {
            setTimeout(function () {
                addGuides<?php echo $caUid; ?>();
            }, 1000);
        }
    }

    function addGuide<?php echo $caUid; ?>(type, position, horizontal) {
        if (horizontal) {
            eval('var w = width' + type + ';');
            var left = (widthTV - w) / 2;
            var propotionalLeft = (left * factorW);
            var elementID = 'gudeHorizontal' + type + position;
            $('<?php echo '#' . $caUid; ?> .cr-viewport').append('<div id="' + elementID + '" class="gudeHorizontal"></div>');
            if (position == 1) {
                $('#' + elementID).css('left', propotionalLeft + 'px');
            } else {
                $('#' + elementID).css('right', propotionalLeft + 'px');
            }
        } else {
            var top = (heightTV - heightDevices) / 2;
            var propotionalTop = (top * factorW);
            var elementID = 'gudeVertical' + type + position;
            $('<?php echo '#' . $caUid; ?> .cr-viewport').append('<div id="' + elementID + '" class="gudeVertical"></div>');
            if (position == 1) {
                $('#' + elementID).css('top', propotionalTop + 'px');
            } else {
                $('#' + elementID).css('bottom', propotionalTop + 'px');
            }
        }
    }

</script>