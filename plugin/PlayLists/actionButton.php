<?php
global $advancedCustom;
$crc = uniqid();
?>
    <?php if ((empty($_POST['disableAddTo'])) && (( ($advancedCustom != false) && ($advancedCustom->disableShareAndPlaylist == false)) || ($advancedCustom == false))) { ?>
       <a href="#" class="<?php echo $btnClass; ?>" id="addBtn<?php echo $videos_id . $crc; ?>" onclick="loadPlayLists('<?php echo $videos_id; ?>', '<?php echo $crc; ?>');">
            <span class="fa fa-plus"></span> 
            <span class="hidden-xs"><?php echo __("Add to"); ?></span>
        </a>
        <div class="webui-popover-content" >
            <?php if (User::isLogged()) { ?>
                <form role="form">
                    <div class="form-group">
                        <input class="form-control" id="searchinput<?php echo $videos_id.$crc; ?>" type="search" placeholder="<?php echo __("Search"); ?>..." />
                    </div>
                    <div class="PlayListList searchlist<?php echo $videos_id.$crc; ?> list-group">
                    </div>
                </form>
                <div>
                    <hr>
                    <div class="form-group">
                        <input id="playListName<?php echo $videos_id . $crc; ?>" class="form-control" placeholder="<?php echo __("Create a New Play List"); ?>"  >
                    </div>
                    <div class="form-group">
                        <?php echo __("Make it public"); ?>
                        <div class="material-switch pull-right">
                            <input id="publicPlayList<?php echo $videos_id . $crc; ?>" name="publicPlayList" type="checkbox" checked="checked"/>
                            <label for="publicPlayList" class="label-success"></label>
                        </div>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-success btn-block" id="addPlayList<?php echo $videos_id . $crc; ?>" ><?php echo __("Create a New Play List"); ?></button>
                    </div>
                </div>
            <?php } else { ?>
                <h5><?php echo __("Want to watch this again later?"); ?></h5>
                <?php echo __("Sign in to add this video to a playlist."); ?>
                <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-primary">
                    <span class="fas fa-sign-in-alt"></span>
                    <?php echo __("Login"); ?>
                </a>
            <?php } ?>
        </div>
        <script>
            $(document).ready(function () {
                loadPlayLists('<?php echo $videos_id; ?>', '<?php echo $crc; ?>');
                $('#addBtn<?php echo $videos_id . $crc; ?>').webuiPopover();
                $('#addPlayList<?php echo $videos_id . $crc; ?>').click(function () {
                    modal.showPleaseWait();
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>objects/playlistAddNew.json.php',
                        method: 'POST',
                        data: {
                            'videos_id': <?php echo $videos_id; ?>,
                            'status': $('#publicPlayList<?php echo $videos_id . $crc; ?>').is(":checked") ? "public" : "private",
                            'name': $('#playListName<?php echo $videos_id . $crc; ?>').val()
                        },
                        success: function (response) {
                            if (response.status>0) {
                                playList = [];
                                reloadPlayLists();
                                loadPlayLists('<?php echo $videos_id; ?>', '<?php echo $crc; ?>');
                                $('#playListName<?php echo $videos_id . $crc; ?>').val("");
                                $('#publicPlayList<?php echo $videos_id . $crc; ?>').prop('checked', true);
                            }
                            modal.hidePleaseWait();
                        }
                    });
                    return false;
                });
            });
        </script>
    <?php } ?>