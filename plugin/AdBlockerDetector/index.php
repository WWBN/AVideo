<?php
global $global, $config;
//$doNotIncludeConfig = 1;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

$global['ignoreAdBlocker'] = 1;
$_page = new Page(array('Disable Ad Blocker'));
?>
<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">

            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h2>
                        <i class="fa-solid fa-ban"></i> We see you’re using an ad blocker.
                    </h2>
                </div>
                <div class="panel-body">
                    <h3>
                        <i class="fa-solid fa-handshake"></i> Please help us continue to provide you with free, quality movie and TV streaming by disabling your ad blocker.
                    </h3>
                </div>

                <div class="panel-footer">
                    <button class="btn btn-primary btn-lg btn-block" onclick="document.location = webSiteRootURL ;"><i class="fa fa-refresh"></i> Refresh Page</button>
                    <p><small><i class="fa fa-info-circle"></i> Please refresh this page once you have disabled the ad blocker.</small></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>