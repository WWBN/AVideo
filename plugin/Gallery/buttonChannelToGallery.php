<?php
$titleBtn1 = __('Add to first page');
$titleBtn2 = __('Remove first page');

$class = '';
if(Gallery::isChannelToGallery($users_id)){
    $class = 'isChannelToGallery';
}
if(!User::isAdmin()){
    return '';
}
?>
<div class="ChannelToGallery<?php echo $users_id; ?> <?php echo $class; ?>" style="display: inline-block;">
    <button class="btn btn-primary btn-xs addChannelToGallery" onclick="channelToGallery(<?php echo $users_id; ?>, 1);"  data-toggle="tooltip" title="<?php echo $titleBtn1; ?>" >
        <i class="fas fa-plus"></i> <small><?php echo $titleBtn1; ?></small>
    </button>
    <button class="btn btn-danger btn-xs removeChannelFromGallery" onclick="channelToGallery(<?php echo $users_id; ?>, 0);"  data-toggle="tooltip" title="<?php echo $titleBtn2; ?>" >
        <i class="fas fa-trash"></i> <small><?php echo $titleBtn2; ?></small>
    </button>
</div>

<script>
    $(document).ready(function () {
    });
</script>