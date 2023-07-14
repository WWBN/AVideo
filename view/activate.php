<?php
require_once dirname(__FILE__) . '/../videos/configuration.php';
$_page = new Page(array('Activate'));
?>
<style>
.loginCode{
    font-size: 6em;
    font-weight: bolder;
}
</style>
<div class="container">
    <div class="panel panel-default">
        <div class="panel panel-body text-center ">
            <strong class="loginCode"><?php echo getRandomCode(); ?></strong>
        </div>
    </div>
</div>
<?php
$_page->print();
?>