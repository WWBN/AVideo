<script>
    function getIMDb(id, what) {
        modal.showPleaseWait();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/IMDbScrape/get.json.php?videos_id=' + id+'&what='+what,
            success: function (response) {
                if(response.error){
                    console.log(response.msg);
                }
                modal.hidePleaseWait();
            }
        });
    }
    $(document).ready(function () {
        

    });

</script>