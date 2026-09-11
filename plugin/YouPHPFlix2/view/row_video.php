<div class="poster rowVideo" id="poster<?php echo $uid; ?>" poster="<?php echo $poster; ?>"
    style="
     display: none;
     -webkit-background-size: cover;
     -moz-background-size: cover;
     -o-background-size: cover;
     background-size: cover;
     background-image: url('<?php echo $global['webSiteRootURL']; ?>plugin/YouPHPFlix2/view/img/loading.gif');
     ">
    <!-- row video -->
    <div class="posterDetails">
        <button type="button" class="btn btn-default flix-close-details" aria-label="<?php echo __('Close'); ?>">
            <i class="fa fa-times" aria-hidden="true"></i>
        </button>
        <h2 class="infoTitle">
            <?php echo $value['title']; ?>
        </h2>
        
        <?php
        include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row_info.php';
        ?>
    </div>
</div>