<li>
    <a href="<?php echo "{$global['webSiteRootURL']}plugin/Live"; ?>"  data-toggle="tooltip" title="<?php echo __($buttonTitle); ?>" data-placement="left"  
       class="faa-parent animated-hover">
        <i class="fas fa-circle faa-flash" style="color: red;" ></i>  <?php echo __($buttonTitle); ?>
    </a>
</li>
<li>
    <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'plugin/WebRTC/');return false;"  data-toggle="tooltip" title="<?php echo  __('Go Live'); ?>" data-placement="left"  
       class="faa-parent animated-hover">
        <i class="fa-solid fa-camera"></i>  <?php echo __('Go Live'); ?>
    </a>
</li>