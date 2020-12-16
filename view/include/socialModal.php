<?php
$social = getSocialModal($video['id'], @$url, @$title);
?>
<button class="btn btn-primary" onclick="showSharing<?php echo $social['id']; ?>()">
    <span class="fa fa-share"></span> <?php echo __("Share"); ?>
</button>
<?php echo $social['html']; ?>