<?php
$social = getSocialModal($video['id'], @$url, @$title);
?>
<button class="btn btn-primary <?php echo $class; ?>" onclick="showSharing<?php echo $social['id']; ?>()">
    <span class="fa fa-share"></span> 
    <span class="hidden-sm hidden-xs"><?php echo __("Share"); ?></span>
</button>
<?php echo $social['html']; ?>