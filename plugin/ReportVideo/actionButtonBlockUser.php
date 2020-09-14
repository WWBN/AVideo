<?php
if (empty($users_id)) {
    return '';
}
if (!User::isLogged()) {
    return '';
}
if ($users_id== User::getId()) {
    return '';
}
?>
<span class="btn btn-default no-outline pull-right" id="reportUserBtn"  data-toggle="tooltip" title="<?php if (!User::isLogged()) { echo __("You need to sign in to block this user"); }else{ echo __("Block and hide this user content"); } ?>" >
    <i class="fas fa-ban"></i> <small class="hidden-md hidden-sm hidden-xs"><?php echo __('Block User'); ?></small>
</span>

<?php if (User::isLogged()) { ?>
<span class="btn btn-danger no-outline pull-right" id="unreportUserBtn">
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
                        .then((willDelete) => {
                            if (willDelete) {

                                modal.showPleaseWait();
                                $.ajax({
                                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/ReportVideo/block.json.php?unblock=1',
                                    method: 'POST',
                                    data: {'users_id': '<?php echo $users_id; ?>'},
                                    success: function (response) {
                                        setTimeout(function () {
                                            modal.hidePleaseWait();
                                            if (response.error) {
                                                avideoAlert("<?php echo __("Error"); ?>", response.msg, "error");
                                            } else {
                                                showBlockButtons(false);
                                                avideoAlert("<?php echo __("Success!"); ?>", response.msg, "success");
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
                        .then((willDelete) => {
                            if (willDelete) {

                                modal.showPleaseWait();
                                $.ajax({
                                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/ReportVideo/block.json.php',
                                    method: 'POST',
                                    data: {'users_id': '<?php echo $users_id; ?>'},
                                    success: function (response) {
                                        setTimeout(function () {
                                            modal.hidePleaseWait();
                                            if (response.error) {
                                                avideoAlert("<?php echo __("Error"); ?>", response.msg, "error");
                                            } else {
                                                showBlockButtons(true);
                                                avideoAlert("<?php echo __("Success!"); ?>", response.msg, "success");
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