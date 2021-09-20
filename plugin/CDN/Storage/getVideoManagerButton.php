<div class="panel panel-default">
    <div class="panel-heading"><?php echo __('CDN Storage selected videos'); ?></div>
    <div class="panel-body">
        <div class="btn-group">
            <button class="btn btn-primary moveStorageGlobal btn-sm" siteId='-1'><i class="fas fa-map-marker-alt"></i> Local  <br><small><?php echo $global['webSiteRootURL']; ?></small></button>     
            
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {

        $('.moveStorageGlobal').click(function () {
            var sites_id = parseInt($(this).attr('siteId'));
            var videos_ids = getSelectedVideos();
            if (videos_ids.length === 0) {
                avideoAlert("<?php echo __("Error"); ?>", "Please select some videos", "error");
                return false;
            }
            if (sites_id) {
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/YPTStorage/bulkMove.json.php',
                    data: {'videos_ids': videos_ids, sites_id: sites_id},
                    type: 'post',
                    success: function (response) {
                        modal.hidePleaseWait();
                        if (!response.error) {
                            avideoAlert("<?php echo __("Congratulations!"); ?>", response.msg, "success");
                            setTimeout(function(){
                                $('#storageProgressModal').modal('hide');
                                $("#grid").bootgrid('reload');
                            },1000);
                        } else {
                            var msg = response.msg + "<br>";

                            if (typeof response.videos_failed === 'object') {
                                for (x in response.videos_failed) {
                                    console.log(typeof response.videos_failed[x]);
                                    if (typeof response.videos_failed[x] === 'object') {
                                        msg += "videos_id:[" + response.videos_failed[x].videos_id + "] - " + response.videos_failed[x].msg + "<br>";
                                    }
                                }
                            }
                            avideoAlert("<?php echo __("Error"); ?>", msg, "error");
                        }
                    }
                });
            }
        });

    });

</script>


