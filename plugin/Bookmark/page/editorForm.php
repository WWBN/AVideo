<form id="bookmarkForm">
    <input type="hidden" id="inputId" name="id" >
    <input id="videos_id" name="videos_id" type="hidden">
    <div class="row">
        <?php
        $autoComplete = Layout::getVideoAutocomplete(0, 'videoAutocomplete');
        ?>
    </div>
    <div class="form-group">
        <label for="inputBookmark">Bookmark</label>
        <input id="inputBookmark" placeholder="<?php echo __("Bookmark"); ?>" class="form-control" name='name'>
    </div>
    <div class="form-group">
        <label for="inputTime">Time in Sec.</label>
        <input type="number" step="1" class="form-control" id="inputTime" name="timeInSeconds" placeholder="Bookmark Time" >
    </div>
    <hr>
    <div class="col-md-4">
        <button class="btn btn-primary btn-block" onclick="clearBookmarkForm();return false;"><i class="fa fa-file"></i> Clear</button>
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-success btn-block"><i class="fa fa-save"></i> Save</button>
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-success btn-block" onclick="$('#inputId').val('');"><i class="fa fa-save"></i> Save as</button>
    </div>

</form>
<script>
    var bookmarkTable;
    $(document).ready(function () {

        $('#bookmarkForm').submit(function (evt) {
            evt.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL+'plugin/Bookmark/page/bookmarkSave.json.php',
                data: $('#bookmarkForm').serializeArray(),
                type: 'post',
                success: function (response) {
                    modal.hidePleaseWait();
                    if (!response.error) {
                        avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your plan has been saved!"); ?>", "success");
                    } else {
                        avideoAlert("<?php echo __("Your plan could not be saved!"); ?>", response.error, "error");
                    }
                    bookmarkTable.ajax.reload();
                    clearBookmarkForm();
                }
            });
            return false;
        });

        $("#inputVideo").autocomplete({
            minLength: 0,
            source: function (req, res) {
                $.ajax({
                    url: webSiteRootURL+'videos.json',
                    type: "POST",
                    data: {
                        searchPhrase: req.term,
                        current: 1,
                        rowCount: 10
                    },
                    success: function (data) {
                        res(data.rows);
                    }
                });
            },
            focus: function (event, ui) {
                $("#inputVideo").val(ui.item.title);
                return false;
            },
            select: function (event, ui) {
                $("#inputVideo").val(ui.item.title);
                $("#inputVideo-poster").attr("src", ui.item.videosURL.jpg_thumbsV2.url);
                $('#videos_id').val(ui.item.id);
                return false;
            }
        }).autocomplete("instance")._renderItem = function (ul, item) {
            return $("<li>").append("<div>" + item.title + "<br><?php echo __("Uploaded By"); ?>: " + item.user + "</div>").appendTo(ul);
        };
    });

    function clearBookmarkForm() {
        $('#bookmarkForm')[0].reset();
        $('#inputVideo-poster').attr('src','<?php echo ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_URL); ?>');
    }
</script>