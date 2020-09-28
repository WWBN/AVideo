<?php
if(!User::userCanBlockUser($users_id, true)){
    return '';
}
?>
<span class="btn btn-default no-outline pull-right" style="display: none;" id="reportUserBtn"  data-toggle="tooltip" title="<?php if (!User::isLogged()) { echo __("You need to sign in to block this user"); }else{ echo __("Block and hide this user content"); } ?>" >
    <i class="fas fa-ban"></i> <small class="hidden-md hidden-sm hidden-xs"><?php echo __('Block User'); ?></small>
</span>

<?php if (User::isLogged()) { ?>
<span class="btn btn-danger no-outline pull-right" style="display: none;" id="unreportUserBtn">
    <i class="fas fa-ban"></i> <small class="hidden-md hidden-sm hidden-xs"><?php echo __('Unblock User'); ?></small>
</span>

<?php } ?>
<script>
    function showBlockButtons(isBlocked){
        if(isBlocked){
            $('#reportUserBtn').hide();
            $('#unreportUserBtn').show();
        }else{
            $('#reportUserBtn').show();
            $('#unreportUserBtn').hide();
        }
    }
    $(document).ready(function () {
<?php if (User::isLogged()) { ?>
            showBlockButtons(<?php echo ReportVideo::isBlocked($users_id)?"true":"false"; ?>);
            $("#unreportUserBtn").click(function () {
                swal({
                    title: "<?php echo __("Are you sure?"); ?>",
                    text: "<?php echo __("Do you want to unblock this user?"); ?>",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                        .then(function(willDelete) {
                            if (willDelete) {

                                modal.showPleaseWait();
                                $.ajax({
                                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/ReportVideo/block.json.php?unblock=1&<?php echo User::loginFromRequestToGet(); ?>',
                                    method: 'POST',
                                    data: {'users_id': '<?php echo $users_id; ?>'},
                                    success: function (response) {
                                        setTimeout(function () {
                                            if (response.error) {
                                                modal.hidePleaseWait();
                                                avideoAlert("<?php echo __("Error"); ?>", response.msg, "error");
                                            } else {
                                                showBlockButtons(false);
                                                //avideoAlert("<?php echo __("Success!"); ?>", response.msg, "success");
                                                document.location = "<?php echo getSelfURI(); ?>";
                                            }
                                        }, 500);
                                        //                                  
                                    }
                                });

                            }
                        });
                return false;
            });
            
            $("#reportUserBtn").click(function () {
                swal({
                    title: "<?php echo __("Are you sure?"); ?>",
                    text: "<?php echo __("Do you want to block this user?"); ?>",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                        .then(function(willDelete) {
                            if (willDelete) {

                                modal.showPleaseWait();
                                $.ajax({
                                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/ReportVideo/block.json.php?<?php echo User::loginFromRequestToGet(); ?>',
                                    method: 'POST',
                                    data: {'users_id': '<?php echo $users_id; ?>'},
                                    success: function (response) {
                                        setTimeout(function () {
                                            if (response.error) {
                                                modal.hidePleaseWait();
                                                avideoAlert("<?php echo __("Error"); ?>", response.msg, "error");
                                            } else {
                                                showBlockButtons(true);
                                                //avideoAlert("<?php echo __("Success!"); ?>", response.msg, "success");
                                                document.location = "<?php echo getSelfURI(); ?>";
                                            }
                                        }, 500);
                                        //                                  
                                    }
                                });

                            }
                        });
                return false;
            });
<?php } else { ?>
            $("#reportUserBtn").click(function () {
                $(this).tooltip("show");
                return false;
            });
<?php } ?>
    });
</script>