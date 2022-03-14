<?php
if(!User::userCanBlockUser($users_id, true)){
    return '';
}
?>
<button class="btn btn-default btn-xs" style="display:none;" id="reportUserBtn<?php echo $users_id; ?>"  data-toggle="tooltip" title="<?php if (!User::isLogged()) { echo __("You need to sign in to block this user"); }else{ echo __("Block and hide this user content"); } ?>" >
    <i class="fas fa-ban"></i> <small><?php echo __('Block User'); ?></small>
</button>

<?php if (User::isLogged()) { ?>
<button class="btn btn-danger btn-xs" style="display:none;" id="unreportUserBtn<?php echo $users_id; ?>">
    <i class="fas fa-ban"></i> <small><?php echo __('Unblock User'); ?></small>
</button>

<?php } ?>
<script>
    function showBlockButtons<?php echo $users_id; ?>(isBlocked){
        if(isBlocked){
            $('#reportUserBtn<?php echo $users_id; ?>').hide();
            $('#unreportUserBtn<?php echo $users_id; ?>').show();
        }else{
            $('#reportUserBtn<?php echo $users_id; ?>').show();
            $('#unreportUserBtn<?php echo $users_id; ?>').hide();
        }
    }
    $(document).ready(function () {
<?php if (User::isLogged()) { ?>
            showBlockButtons<?php echo $users_id; ?>(<?php echo ReportVideo::isBlocked($users_id)?"true":"false"; ?>);
            $("#unreportUserBtn<?php echo $users_id; ?>").click(function () {
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
                                                showBlockButtons<?php echo $users_id; ?>(false);
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

            $("#reportUserBtn<?php echo $users_id; ?>").click(function () {
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
                                                showBlockButtons<?php echo $users_id; ?>(true);
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
            $("#reportUserBtn<?php echo $users_id; ?>").click(function () {
                $(this).tooltip("show");
                return false;
            });
<?php } ?>
    });
</script>