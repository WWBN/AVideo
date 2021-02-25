<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isLogged()) {
    forbiddenPage();
}
?>
<form id="search-form" name="search-form" action="<?php echo $global['webSiteRootURL'].''; ?>" method="get">
    <div id="custom-search-input">
        <div class="input-group col-md-12">
            <input type="search" name="searchPhrase" class="form-control input-lg" placeholder="<?php echo __('Search Videos'); ?>" value="">
            <span class="input-group-btn">
                <button class="btn btn-info btn-lg" type="submit">
                    <i class="glyphicon glyphicon-search"></i>
                </button>
            </span>
        </div>
    </div>
</form>
<script>
    function searchVideos() {
        avideoAlertHTMLText("<?php echo __('Search Video'); ?>", "");
    }
</script>