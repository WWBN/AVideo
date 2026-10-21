<script>
    $(document).ready(function() {
        <?php if (empty($advancedCustom->showShareMenuOpenByDefault)) { ?>
            $("#shareDiv").slideUp();
        <?php } ?>
        $("#shareBtn").click(function() {
            $(".menusDiv").not("#shareDiv").slideUp();
            $("#shareDiv").slideToggle();
            return false;
        });
    });
</script>
