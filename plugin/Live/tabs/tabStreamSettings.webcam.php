<div class="tabbable-line ">
    <ul class="nav nav-tabs">
        <li class="active" data-toggle="tooltip" data-placement="bottom" title="<?php echo __('Stream Software'); ?>">
            <a data-toggle="tab" href="#tabStreamSoftware">
                <i class="fa-solid fa-key"></i>
                <?php echo __('Stream Software'); ?>
            </a>
        </li>
        <li class="" data-toggle="tooltip" data-placement="bottom" title="<?php echo __('Webcam'); ?>">
            <a data-toggle="tab" href="#tabWebcam">
                <i class="fa-solid fa-camera"></i>
                <?php echo __('Webcam'); ?>
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="tabStreamSoftware" class="tab-pane fade in active">
        <?php
            include $global['systemRootPath'] . 'plugin/Live/tabs/tabStreamSettings.software.php';
            ?>
        </div>
        <div id="tabWebcam" class="tab-pane fade ">
            <?php
            include $global['systemRootPath'] . 'plugin/WebRTC/panel.php';
            ?>
        </div>
    </div>
</div>