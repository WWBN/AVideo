<?php
global $advancedCustom, $global;
doNOTOrganizeHTMLIfIsPagination();
$global['laodPlaylistScript'] = 1;
?>
<!-- <?php echo __FILE__; ?> -->
<?php if ((empty($_POST['disableAddTo'])) && ((($advancedCustom != false) && ($advancedCustom->disableShareAndPlaylist == false)) || ($advancedCustom == false))) { ?>
    <a href="#" class="<?php echo $btnClass; ?>" id="addBtn<?php echo $videos_id; ?>" onclick="actionButtonPlaylistClick(this, '<?php echo $videos_id; ?>'); return false;" data-toggle="tooltip" title="<?php echo __("Add to"); ?>">
        <span class="fa fa-plus"></span>
        <?php echo __("Add to"); ?>
    </a>
    <div class="webui-popover-content">
        <?php
        if (User::isLogged()) {
            $uniqId = uniqid();
        ?>
            <form role="form">
                <div class="form-group">
                    <input class="form-control" id="searchinput<?php echo $videos_id; ?>" type="search" placeholder="<?php echo __("Search"); ?>..." />
                </div>
                <div class="PlayListList searchlist<?php echo $videos_id; ?> list-group"><i class="fas fa-spinner fa-spin"></i></div>
            </form>
            <div>
                <div class="form-group">
                    <input id="playListName<?php echo $videos_id; ?>_<?php echo $uniqId; ?>" class="form-control playListName" placeholder="<?php echo __("Create a New"); ?> <?php echo $obj->name; ?>">
                </div>
                <div class="form-group">
                    <?php echo __("Make it public"); ?>
                    <div class="material-switch pull-right">
                        <input id="publicPlayList<?php echo $videos_id; ?>_<?php echo $uniqId; ?>" name="publicPlayList" type="checkbox" checked="checked" />
                        <label for="publicPlayList<?php echo $videos_id; ?>_<?php echo $uniqId; ?>" class="label-success"></label>
                    </div>
                </div>
                <div class="form-group">
                    <button
                        class="btn btn-success btn-block"
                        type="button"
                        id="addPlayList<?php echo $videos_id; ?>"
                        onclick="createNewProgram($('#playListName<?php echo $videos_id; ?>_<?php echo $uniqId; ?>').val(), $('#publicPlayList<?php echo $videos_id; ?>_<?php echo $uniqId; ?>').is(':checked'), <?php echo $videos_id; ?>);return false;">
                        <?php echo __("Create a New"); ?> <?php echo $obj->name; ?>
                    </button>
                </div>
            </div>
        <?php } else { ?>
            <strong><?php echo __("Want to watch this again later?"); ?></strong>
            <br>
            <?php echo __("Sign in to add this video to a playlist."); ?>
            <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-primary">
                <span class="fas fa-sign-in-alt"></span>
                <?php echo __("Login"); ?>
            </a>
        <?php } ?>
    </div>
<?php } ?>