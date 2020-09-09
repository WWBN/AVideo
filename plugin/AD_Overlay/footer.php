<div id="adsFormModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <?php echo __("Ad Overlay Code"); ?> 
                    <span class="label label-success adsStatus">Ads Active</span>
                    <span class="label label-danger adsStatus">Ads Inacitive</span>
                </h4>
            </div>
            <div class="modal-body">
                <textarea class="form-control" rows="10" id="addOverlayCode"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo __("Close"); ?></button>
                <button class="btn btn-success " type="button" onclick="saveCode(true, false)"><?php echo __("Approve Ad Code") ?></button>
                <button class="btn btn-warning" type="button" onclick="saveCode(false, false)"><?php echo __("Disapprove Ad Code") ?></button>
                <button class="btn btn-danger" type="button" onclick="saveCode(false, true)"><?php echo __("Disapprove and Delete Ad Code") ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<script>
    var ad_overlay_users_id
    function adsUser(users_id){
        modal.showPleaseWait();
        $('.adsStatus').hide();
        $( "#addOverlayCode" ).text('');
        ad_overlay_users_id = users_id;
        $('#adsFormModal').modal();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/AD_Overlay/getCode.json.php?users_id=' + users_id,
            success: function (response) {
                $( "#addOverlayCode" ).text(response.msg);
                if(response.status == 'a'){
                    $('.adsStatus.label-success').fadeIn();
                }else{
                    $('.adsStatus.label-danger').fadeIn();
                }
                modal.hidePleaseWait();
            }
        });
    }

    function saveCode(approveAd, deleteAd) {
        modal.showPleaseWait();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/AD_Overlay/saveCode.json.php',
            data: {
                "addOverlayCode": $('#addOverlayCode').val(),
                "users_id": ad_overlay_users_id,
                "approveAd": approveAd,
                "deleteAd": deleteAd
            },
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlert("<?php echo __("Sorry!"); ?>", response.error, "error");
                    modal.hidePleaseWait();
                } else {
                    avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your code has been saved!"); ?>", "success");
                    adsUser(ad_overlay_users_id);
                }
                //modal.hidePleaseWait();
            }
        });
    }


    $(document).ready(function () {


    });

</script>