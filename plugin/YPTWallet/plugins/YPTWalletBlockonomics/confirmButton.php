<button type="submit" class="btn btn-primary" id="YPTWalletBlockonomicsButton"><i class="fab fa-bitcoin"></i> Bitcoin</button>
<script>
    $(document).ready(function () {
        $('#YPTWalletBlockonomicsButton').click(function (evt) {
            evt.preventDefault();
            modal.showPleaseWait();
            document.location = '<?php echo $global['webSiteRootURL']; ?>plugin/BlockonomicsYPT/invoice.php?value='+$('#value').val();
        });
    });

</script>