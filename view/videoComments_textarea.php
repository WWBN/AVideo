<?php
$className = '';
if (empty($video['id'])) {
    $video['id'] = 0;
    $className = 'hidden';
}
$maxLen = empty($advancedCustom->commentsMaxLength) ? 200 : $advancedCustom->commentsMaxLength;

$disabled = '';
$content = '';
$commentButtonText = '<i class="fas fa-comment"></i> <span class="hidden-md hidden-sm hidden-xs">' . __("Comment") . '</span>';
$button = '<button class="btn btn-success btn-block" id="saveCommentBtn" style="height: 72px;" onclick="saveComment();">' . $commentButtonText . '</button>';
$js = "setupFormElement('#comment', 5, commentsmaxlen, true, true);";
if (!User::canComment()) {
    $js = "";
    $disabled = "disabled='disabled'";
    if (User::isLogged()) {
        $commentButtonText = '<i class="fas fa-comment-slash"></i> <span class="hidden-md hidden-sm hidden-xs">' . __("Verify") . '</span>';
        $content = __("Verify your email to be able to comment");
        $button = '<button class="btn btn-warning btn-block" style="height: 72px;" onclick="document.location=\'' . $global['webSiteRootURL'] . 'user\';" data-toggle="tooltip" title="' . __("Verify your email to be able to comment") . '">' . $commentButtonText . '</a>';
    } else {
        $commentButtonText = '<i class="fas fa-sign-in-alt"></i> <span class="hidden-md hidden-sm hidden-xs">' . __("Login") . '</span>';
        $content = __("You must login to be able to comment on videos");
        $button = '<button class="btn btn-warning btn-block" style="height: 72px;" onclick="document.location=\'' . $global['webSiteRootURL'] . 'user\';" data-toggle="tooltip" title="' . __("Login") . '">' . $commentButtonText . '</a>';
    }
}
?>
<div class="row <?php echo $className; ?>">
    <?php
    if (User::isAdmin()) {
        ?>
        <div class="col-xs-12">
            <label for="comment_users_id"><?php echo __('Select a user to comment as if you were him'); ?></label>
        </div>
        <?php
        $users_autocomplete = Layout::getUserAutocomplete(0, 'comment_users_id');
    }
    ?>
    <div class="col-xs-10 col-lg-9" style="padding-right: 1px;">
        <textarea class="form-control custom-control" rows="3" style="resize:none" id="comment"
                  <?php echo $disabled; ?>><?php
                      echo $content;
                      ?></textarea>
    </div>
    <div class="col-xs-2 col-lg-3" style="padding-left: 1px;">
        <?php echo $button; ?>
    </div>
</div>

<script>
    var commentsmaxlen = <?php echo $maxLen; ?>;
    var commentVideos_id = <?php echo intval($video['id']); ?>;
    $(document).ready(function () {
<?php
echo $js;
?>
    });
</script>