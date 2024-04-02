<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

if (User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}user");
    exit;
}

$_page = new Page(array('Sign Up'));
$_page->setIncludeBGAnimation(true);
?>

<div class="container">
    <br>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-2"></div>
        <div class="col-xs-12 col-sm-12 col-lg-8">
            <?php
            include $global['systemRootPath'] . 'view/userSignUpBody.php';
            ?>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-2"></div>
    </div>
</div><!--/.container-->

<?php
$_page->print();
?>