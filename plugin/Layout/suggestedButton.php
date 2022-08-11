<?php
$video = new Video('', '', $videos_id);
$_class = 'isSuggested btn-warning ';
if (empty($video->getIsSuggested())) {
    $_class = 'isNotSuggested btn-default ';
}
?>
<button type="button" 
        class="suggestBtn <?php echo $_class; ?> <?php echo $class; ?>"  
        onclick="toogleVideoSuggested($(this));return false;"
        videos_id="<?php echo $videos_id; ?>" >
    <span class="hidden-md hidden-sm hidden-xs">
        <span class="unsuggestText btnText"><i class="fas fa-star"></i> <?php echo __("Unsuggest it"); ?></span>
        <span class="suggestText btnText"><i class="far fa-star"></i> <?php echo __("Suggest it"); ?></span></span>
</button>