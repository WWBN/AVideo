<?php
$sharingUid = uniqid();
?>
<button class="btn btn-primary" onclick="showSharing<?php echo $sharingUid ?>()">
    <span class="fa fa-share"></span> <?php echo __("Share"); ?>
</button>
<div id="SharingModal<?php echo $sharingUid ?>" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body">
                <center>
                    <?php
                    include $global['systemRootPath'] . 'view/include/social.php';
                    ?>
                </center>
            </div>
        </div>
    </div>
</div>
<script>
    function showSharing<?php echo $sharingUid ?>() {
        $('#SharingModal<?php echo $sharingUid ?>').appendTo("body");
        $('#SharingModal<?php echo $sharingUid ?>').modal("show");
        return false;
    }

    $(document).ready(function () {
        $('#SharingModal<?php echo $sharingUid ?>').modal({show: false});
    });
</script>