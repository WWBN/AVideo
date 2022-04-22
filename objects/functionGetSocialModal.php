<div id="SharingModal<?php echo $sharingUid ?>" class="modal fade" role="dialog" style="top: 60px;">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body">
                <center>
                    <?php include $global['systemRootPath'] . 'view/include/social.php'; ?>
                </center>
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