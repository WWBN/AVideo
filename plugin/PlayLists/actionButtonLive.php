<?php
$playlists_id = intval(@$_REQUEST['playlists_id_live']);
if(empty($playlists_id)){
    return "";
}
?>
<span id="epg"></span>
<script>
    $(document).ready(function () {
        $("#epg").closest(".row").prepend("<div class='col-sm-12 watch8-action-buttons' id='epgLine' style='display:none;'></div>");
        
        $.ajax({
            url: '<?php echo PlayLists::getLiveEPGLink($playlists_id); ?>',
            success: function (response) {
                $("#epgLine").html(response);
                $("#epgLine").slideDown();
            }
        });
    });
</script>