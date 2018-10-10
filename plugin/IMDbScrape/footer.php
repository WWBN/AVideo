<script>
    function getIMDb(id) {
        modal.showPleaseWait();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/IMDb/get.json.php?videos_id=' + id,
            success: function (response) {
                if(response.error){
                    console.log(response.msg)
                }
            }
        });
    }
    $(document).ready(function () {
        

    });

</script>