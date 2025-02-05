<?php
$search = getSearchVar();
?>
<div class="text-center" id="searchNotFoundImage">
    <img src="<?php echo ImagesPlaceHolders::getVideoNotFoundPoster(ImagesPlaceHolders::$RETURN_URL); ?>" class="img img-responsive center-block ImagesPlaceHoldersDefaultImage" style="max-height: 50vh;">
</div>
<div class="alert alert-warning text-center">
    <i class="fas fa-exclamation-circle"></i>
    <?php echo sprintf(__('No results found for (%s)'), htmlentities($search, ENT_QUOTES, 'UTF-8')); ?>
</div>
<div class="text-center">
    <p><?php echo __('Try refining your search or explore our latest content'); ?></p>
    <a href="<?php echo $global['webSiteRootURL']; ?>" class="btn btn-primary">
        <i class="fas fa-home"></i> <?php echo __('Go to Homepage'); ?>
    </a>
</div>
