<details id="mediaSelector" class="webrtc-settings">
    <summary id="toggleMediaSelectorButton"><?php echo __('Camera and microphone'); ?></summary>
    <fieldset id="webrtcDeviceFields">
        <legend class="sr-only"><?php echo __('Camera and microphone'); ?></legend>
        <div class="row">
            <div class="col-sm-6 form-group">
                <label for="videoSource"><?php echo __('Camera'); ?></label>
                <select id="videoSource" class="form-control"><option value=""><?php echo __('Default'); ?></option></select>
            </div>
            <div class="col-sm-6 form-group">
                <label for="audioSource"><?php echo __('Microphone'); ?></label>
                <select id="audioSource" class="form-control"><option value=""><?php echo __('Default'); ?></option></select>
            </div>
        </div>
        <button type="button" id="applyChanges" class="btn btn-default">
            <i class="fa fa-check" aria-hidden="true"></i> <?php echo __('Update preview'); ?>
        </button>
        <details class="webrtc-advanced">
            <summary><?php echo __('Advanced settings'); ?></summary>
            <button type="button" id="startScreenShare" class="btn btn-default">
                <i class="fa fa-desktop" aria-hidden="true"></i> <?php echo __('Share screen'); ?>
            </button>
            <p class="help-block"><?php echo __('Screen audio depends on your browser and the source you share.'); ?></p>
        </details>
    </fieldset>
</details>
