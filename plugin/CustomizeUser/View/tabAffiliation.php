<?php
$isACompany = User::isACompany();
?>
<div id="<?php echo $tabId; ?>" class="tab-pane fade in" style="padding: 10px 0;">
    <?php
    if($isACompany){
        include $global['systemRootPath'] . 'plugin/CustomizeUser/View/tabAffiliationCompany.php';
    }else{
        include $global['systemRootPath'] . 'plugin/CustomizeUser/View/tabAffiliationAffiliate.php';
    }
    ?>
</div>

