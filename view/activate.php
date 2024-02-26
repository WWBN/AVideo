<?php
require_once dirname(__FILE__) . '/../videos/configuration.php';
if (!User::isLogged()) {
    gotToLoginAndComeBackHere();
}
$_page = new Page(array('Activate'));
$activation = getActivationCode();
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
            <strong class="loginCode"><?php echo $activation['code']; ?></strong>
        </div>
    </div>
</div>
<?php
$_page->print();
?>