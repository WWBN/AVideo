<?php
if (!empty($default_Playlists_id)) {
    $u = new PlayList($default_Playlists_id);
    if (!empty($u->getName())) {
        $name = $u->getName();
    }
}

if (empty($name)) {
    $name = '';
    $default_Playlists_id = 0;
}
?>
<div class="clearfix"></div>
<div class="col-xs-12" style="padding-left: 2px;">
    <input id="Playlist<?php echo $id; ?>" placeholder="<?php echo __("Type to search a playlist"); ?>" class="form-control" name="Playlist<?php echo $id; ?>" value="<?php echo $name; ?>">
    <input type="hidden" id="<?php echo $id; ?>" value="<?php echo $default_Playlists_id; ?>" name="<?php echo $id; ?>">
</div>
<div class="clearfix"></div>
<script>
    function updatePlaylistAutocomplete<?php echo $id; ?>() {
        var data = <?php echo _json_encode((object) $parameters); ?>;
        data.Playlists_id = $('#<?php echo $id; ?>').val();
        var Playlists_id = data.Playlists_id;
        console.log('updatePlaylistAutocomplete<?php echo $id; ?>', data.Playlists_id);
        resetPlaylistAutocomplete<?php echo $id; ?>();
        if (data.Playlists_id && data.Playlists_id !== '0') {
            if (typeof modal === 'object') {
                modal.showPleaseWait();
            }
            $.ajax({
                url: webSiteRootURL + 'objects/playlistsPublic.json.php',
                type: "POST",
                data: data,
                success: function (data) {
                    if (data && data[0]) {
                        $("#Playlist<?php echo $id; ?>").val(data[0].name);
                        $("#<?php echo $id; ?>").val(Playlists_id);
                    }
                    if (typeof modal === 'object') {
                        modal.hidePleaseWait();
                    }
                }
            });
        }
    }

    function resetPlaylistAutocomplete<?php echo $id; ?>() {
        $("#Playlist<?php echo $id; ?>").val('');
        $("#<?php echo $id; ?>").val(0);
    }

    $(document).ready(function () {

        $("#<?php echo $id; ?>").change(function () {
            updatePlaylistAutocomplete<?php echo $id; ?>();
        });

        $("#<?php echo $id; ?>").bind("change", function () {
            updatePlaylistAutocomplete<?php echo $id; ?>();
        });

        $("#Playlist<?php echo $id; ?>").autocomplete({
            minLength: 0,
            source: function (req, res) {
                var data = <?php echo _json_encode((object) $parameters); ?>;
                data.searchPhrase = req.term;
                $.ajax({
                    url: webSiteRootURL + 'objects/playlistsPublic.json.php',
                    type: "POST",
                    data: data,
                    success: function (data) {
                        console.log(data);
                        res(data);
                    }
                });
            },
            focus: function (event, ui) {
                $("#Playlist<?php echo $id; ?>").val(ui.item.name);
                return false;
            },
            select: function (event, ui) {
                $("#Playlist<?php echo $id; ?>").val(ui.item.name);
                $("#<?php echo $id; ?>").val(ui.item.id);
                <?php
                if(!empty($jsFunctionForSelectCallback)){
                    echo $jsFunctionForSelectCallback.';';
                }
                ?>
                return false;
            }
        }).autocomplete("instance")._renderItem = function (ul, item) {
            return $("<li>").append("<div> " + item.name + "</div>").appendTo(ul);
        };
    });
</script>