<script>
    function socketCDNStorageMoved(json) {
        console.log('socketCDNStorageMoved', json);
        avideoToastSuccess(json);
        $("#grid.videosManager").bootgrid('reload');
    }
</script>