
<span class="label label-danger" id="liveViewStatus">OFFLINE</span>
<!--
<span class="label label-default" id="liveViewViews"><i class="fa fa-eye"></i> <span class="liveViewCount" style="font-size: 0.9em;">0</span></span>
-->
<script>
    function getStats() {
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/Live/stats.json.php?Label',
            data: {"name": "<?php echo $streamName; ?>"},
            type: 'post',
            success: function (response) {
                if (!response.error) {
                    $('#liveViewStatus').removeClass('label-danger');
                    $('#liveViewStatus').addClass('label-success');
                    $('#liveViewViews').removeClass('label-default');
                    $('#liveViewViews').addClass('label-primary');
                } else {
                    $('#liveViewStatus').removeClass('label-success');
                    $('#liveViewStatus').addClass('label-danger');
                    $('#liveViewViews').removeClass('label-primary');
                    $('#liveViewViews').addClass('label-default');
                }
                $('.liveViewCount').text(" " + response.nclients);
                $('#liveViewStatus').text(response.msg);
                $('#onlineApplications').text(response.applications.lenght);
                setTimeout(function () {
                    getStats();
                }, 15000);
            }
        });
    }

    $(document).ready(function () {
        getStats();
    });
</script>