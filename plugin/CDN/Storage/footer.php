<script>
    function socketCDNStorageMoved(json) {
        var element = $("#grid.videosManager");
        if(element.length && typeof element.bootgrid === 'function'){
            console.log('socketCDNStorageMoved', json);
            avideoToastSuccess(json);
            element.bootgrid('reload');
        }
    }
</script>