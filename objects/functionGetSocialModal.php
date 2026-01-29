<link href="<?php echo getURL('view/css/social.css'); ?>" rel="stylesheet" type="text/css"/>
<style>
    #SharingModal<?php echo $sharingUid ?> .modal-header {
        padding: 5px 10px;
        min-height: 30px;
        border-bottom: none;
    }
    #SharingModal<?php echo $sharingUid ?> .modal-header .close {
        color: #000;
        opacity: 0.5;
        font-size: 18px;
        font-weight: bold;
        text-shadow: none;
        margin: 0;
        padding: 0;
    }
    #SharingModal<?php echo $sharingUid ?> .modal-header .close:hover {
        opacity: 1;
    }
    #SharingModal<?php echo $sharingUid ?> .modal-title {
        font-size: 14px;
        margin: 0;
        line-height: 1.2;
    }
    @media (max-height: 650px) {
        #SharingModal<?php echo $sharingUid ?> {
            top: 5px !important;
        }
        #SharingModal<?php echo $sharingUid ?> .modal-dialog {
            margin: 5px auto;
        }
        #SharingModal<?php echo $sharingUid ?> .modal-body {
            max-height: calc(100vh - 60px);
            overflow-y: auto;
            padding: 10px 15px;
        }
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
