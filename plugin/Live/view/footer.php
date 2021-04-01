<script>
    function onlineLabelOnline(selector) {
        console.log("Change video to Online");
        $(selector).removeClass('label-warning');
        $(selector).removeClass('label-danger');
        $(selector).addClass('label-success');
        $(selector).text("<?php echo __("ONLINE"); ?>");
    }

    function onlineLabelPleaseWait(selector) {
        console.log("Change video to please wait");
        $(selector).removeClass('label-success');
        $(selector).removeClass('label-danger');
        $(selector).addClass('label-warning');
        $(selector).text("<?php echo __("Please Wait ..."); ?>");
    }

    function onlineLabelOffline(selector) {
        console.log("Change video to offline");
        $(selector).removeClass('label-warning');
        $(selector).removeClass('label-success');
        $(selector).addClass('label-danger');
        $(selector).text("<?php echo __("OFFLINE"); ?>");
    }
    function onlineLabelFinishing(selector) {
        console.log("Change video to finishing");
        $(selector).removeClass('label-warning');
        $(selector).removeClass('label-success');
        $(selector).addClass('label-danger');
        $(selector).text("<?php echo __("Finishing Live..."); ?>");
    }
</script>