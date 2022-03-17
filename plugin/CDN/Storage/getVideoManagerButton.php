<div class="btn-group">
    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
        <i class="fas fa-project-diagram"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __('Storage'); ?></span> <span class="caret"></span>
    </button>                        
    <ul class="dropdown-menu" role="menu">
        <li>
            <a href="#"  onclick="uploadSelected(); return false;">
                <i class="fas fa-cloud-upload-alt"></i>
                <?php echo __('Upload selected'); ?>
            </a>  
        </li>
        <li>
            <a href="#"  onclick="downloadSelected(); return false;">
                <i class="fas fa-cloud-download-alt"></i>
                <?php echo __('Download selected'); ?>
            </a>  
        </li>
    </ul>
</div>
<script>
    function uploadSelected() {
        var videos_ids = getSelectedVideos();
        if (videos_ids.length === 0) {
            avideoAlertError("Please select some videos");
            return false;
        }

        modal.showPleaseWait();
        var url = webSiteRootURL + 'plugin/CDN/Storage/moveLocalToRemote.json.php';
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                'videos_id': videos_ids
            },
            success: function (response) {
                console.log('uploadSelected', response);
            }
        });

        setTimeout(function () {
            modal.hidePleaseWait();
            avideoToastSuccess('<?php echo __("Processing!"); ?>');
            $("#grid").bootgrid('reload');
        }, 1000);
    }
    
    function downloadSelected() {
        var videos_ids = getSelectedVideos();
        if (videos_ids.length === 0) {
            avideoAlertError("Please select some videos");
            return false;
        }

        modal.showPleaseWait();
        var url = webSiteRootURL + 'plugin/CDN/Storage/moveRemoteToLocal.json.php';
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                'videos_id': videos_ids
            },
            success: function (response) {
                console.log('uploadSelected', response);
            }
        });

        setTimeout(function () {
            modal.hidePleaseWait();
            avideoToastSuccess('<?php echo __("Processing!"); ?>');
            $("#grid").bootgrid('reload');
        }, 1000);
    }

</script>


