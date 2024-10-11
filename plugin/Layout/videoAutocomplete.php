<?php
if (!empty($default_videos_id)) {
    $u = new Video('', '', $default_videos_id);
    if (!empty($u->getvideo())) {
        $name = $u->getTitle();
        $video_image = Video::getPoster($default_videos_id);
    }
}

if (empty($name)) {
    $name = '';
    $video_image = ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_URL);
    $default_videos_id = 0;
}
?>
<div class="clearfix"></div>
<div class="col-xs-2 text-center"  style="padding: 0;">
    <img id="videoAutocomplete-img<?php echo $id; ?>" src="<?php echo $video_image; ?>" class="img img-responsive" 
        style="min-height: 35px; min-width: 50px; max-height: 50px; width: 100%; object-fit: contain; margin-bottom: 5px; display: inline-flex;" 
        alt="video Photo">
</div>
<div class="col-xs-10">
    <input id="videoAutocomplete<?php echo $id; ?>" placeholder="<?php echo __("video"); ?>" class="form-control" name="video<?php echo $id; ?>" value="<?php echo $name; ?>">
    <input type="hidden" id="<?php echo $id; ?>" value="<?php echo $default_videos_id; ?>" name="<?php echo $id; ?>">
</div>


<div class="clearfix"></div>
<script>
    function updateVideoAutocomplete<?php echo $id; ?>() {
        var data = <?php echo _json_encode((object) $parameters); ?>;
        data.videos_id = $('#<?php echo $id; ?>').val();
        var videos_id = data.videos_id;
        console.log('updateVideoAutocomplete<?php echo $id; ?>', data.videos_id);
        resetvideoAutocomplete<?php echo $id; ?>();
        if (data.videos_id && data.videos_id !== '0') {
            if (typeof modal === 'object') {
                modal.showPleaseWait();
            }
            data.showAll=1;
            $.ajax({
                url: webSiteRootURL + 'objects/videos.json.php',
                type: "POST",
                data: data,
                success: function (data) {
                    if (data.rows && data.rows[0]) {
                        $("#videoAutocomplete<?php echo $id; ?>").val(data.rows[0].title);
                        $("#<?php echo $id; ?>").val(videos_id);
                        var photoURL = data.rows[0].videosURL.jpg.url;
                        $("#videoAutocomplete-img<?php echo $id; ?>").attr("src", photoURL);
                    }
                    if (typeof modal === 'object') {
                        modal.hidePleaseWait();
                    }
                }
            });
        }
    }

    function resetvideoAutocomplete<?php echo $id; ?>() {
        $("#videoAutocomplete<?php echo $id; ?>").val('');
        $("#<?php echo $id; ?>").val(0);
        var photoURL = '<?php echo ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_URL); ?>'
        $("#videoAutocomplete-img<?php echo $id; ?>").attr("src", photoURL);
    }

    $(document).ready(function () {

        $("#<?php echo $id; ?>").change(function () {
            updateVideoAutocomplete<?php echo $id; ?>();
        });

        $("#<?php echo $id; ?>").bind("change", function () {
            updateVideoAutocomplete<?php echo $id; ?>();
        });

        $("#videoAutocomplete<?php echo $id; ?>").autocomplete({
            minLength: 0,
            source: function (req, res) {
                var data = <?php echo _json_encode((object) $parameters); ?>;
                data.searchPhrase = req.term;
                data.rowCount = 12;
                data.showAll=1;
                $.ajax({
                    url: webSiteRootURL + 'objects/videos.json.php',
                    type: "POST",
                    data: data,
                    success: function (data) {
                        res(data.rows);
                    }
                });
            },
            focus: function (event, ui) {
                $("#videoAutocomplete<?php echo $id; ?>").val(ui.item.title);
                return false;
            },
            select: function (event, ui) {
                $("#videoAutocomplete<?php echo $id; ?>").val(ui.item.title);
                $("#<?php echo $id; ?>").val(ui.item.id);
                var photoURL = '<?php echo ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_URL); ?>'
                if (ui.item.videosURL.jpg.url) {
                    photoURL = ui.item.videosURL.jpg.url;
                }
                $("#videoAutocomplete-img<?php echo $id; ?>").attr("src", photoURL);
                <?php
                if(!empty($jsFunctionForSelectCallback)){
                    echo $jsFunctionForSelectCallback.';';
                }
                ?>
                return false;
            }
        }).autocomplete("instance")._renderItem = function (ul, item) {
            return $("<li>").append("<div> " + item.title + "</div>").appendTo(ul);
        };
    });
</script>