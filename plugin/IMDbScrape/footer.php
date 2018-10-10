<script>
    function getIMDb(id) {
        modal.showPleaseWait();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/IMDbScrape/get.json.php?videos_id=' + id,
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