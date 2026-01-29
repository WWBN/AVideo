<link href="<?php echo getURL('view/css/social.css'); ?>" rel="stylesheet" type="text/css"/>
<style>
    #SharingModal<?php echo $sharingUid ?> .modal-header .close {
        color: #000;
        opacity: 0.5;
        font-size: 28px;
        font-weight: bold;
        text-shadow: none;
    }
    #SharingModal<?php echo $sharingUid ?> .modal-header .close:hover {
        opacity: 0.8;
    }
</style>
<div id="SharingModal<?php echo $sharingUid ?>" class="modal fade" role="dialog" style="top: 60px;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Share</h4>
            </div>
            <div class="modal-body text-center">
                <?php include $global['systemRootPath'] . 'view/include/social.php'; ?>
            </div>
        </div>
    </div>
</div>
<script>
    function showSharing<?php echo $sharingUid ?>() {
        if ($('#mainVideo').length) {
            $('#SharingModal<?php echo $sharingUid ?>').appendTo("#mainVideo");
        } else {
            $('#SharingModal<?php echo $sharingUid ?>').appendTo("body");
        }
        $('#SharingModal<?php echo $sharingUid ?>').modal("show");
        $('.modal-backdrop').hide();

        return false;
    }

    $(document).ready(function () {
        $('#SharingModal<?php echo $sharingUid ?>').modal({show: false});
    });
</script>
