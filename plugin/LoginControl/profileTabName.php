<?php
$obj = AVideoPlugin::getObjectData("LoginControl");
?>
<li>
    <a data-toggle="tab" href="#loginHistory" id="aloginControl">
        <?php echo __("Login History") ?>
    </a>
</li>
<?php 
if($obj->enablePGP2FA){
?>
<li>
    <a data-toggle="tab" href="#pgp2fa" id="aloginControlpgp2fa">
        <?php echo __("PGP 2FA") ?>
    </a>
</li>
<?php
}
?>