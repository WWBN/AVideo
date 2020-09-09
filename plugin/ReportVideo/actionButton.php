
<span class="btn btn-default no-outline pull-right" id="reportBtn" <?php if (!User::isLogged()) { ?> data-toggle="tooltip" title="<?php echo __("Do you want to report this video? Sign in to make your opinion count."); ?>" <?php } ?>>
    <i class="fas fa-flag"></i> <small><?php echo __('Report'); ?></small>
</span>

<script>
    $(document).ready(function () {
<?php if (User::isLogged()) { ?>
            $("#reportBtn").click(function () {
                        
                        swal({
                title: "<?php echo __("Are you sure?"); ?>",
                    text: "<?php echo __("Do you want to report this video as inapropriate?"); ?>",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
              if (willDelete) {
                  
                            modal.showPleaseWait();
                            $.ajax({
                                url: '<?php echo $global['webSiteRootURL']; ?>plugin/ReportVideo/report.json.php',
                                method: 'POST',
                                data: {'videos_id': '<?php echo $video['id']; ?>'},
                                success: function (response) {
                                    setTimeout(function () {
                                        modal.hidePleaseWait();
                                        if (response.error) {
                                            avideoAlert("<?php echo __("Error"); ?>", response.msg, "error");
                                        } else {
                                            avideoAlert("<?php echo __("Thanks"); ?>", response.msg, "success");
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
            $("#reportBtn").click(function () {
                $(this).tooltip("show");
                return false;
            });
<?php } ?>
    });
</script>