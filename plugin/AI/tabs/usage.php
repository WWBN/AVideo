<table id="responsesUsage-list" class="table table-bordered table-hover">
    <thead>
        <!-- Headers will be added here dynamically -->
    </thead>
    <tbody>
        <!-- Rows will be added here dynamically -->
    </tbody>
</table>

<script>
    var modalloadAIUsage = getPleaseWait();
    function loadAIUsage() {
        modalloadAIUsage.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/AI/tabs/usage.json.php',
            data: {
                videos_id: <?php echo $videos_id; ?>
            },
            type: 'post',
            success: function(response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {

                    var columnOrder = [
                        'type',
                        'sortDate',
                        'price',
                        'videos_id',
                    ];

                    var columnHeaders = {
                        'type': 'type',
                        'sortDate': 'When',
                        'price': 'Cost',
                        'videos_id': 'Video ID',
                    };
                    var columnCallbackFunctions = [];
                    var selector = '#responsesUsage-list';
                    //console.log(selector, response);
                    processAIResponse(selector, response, columnOrder, columnHeaders, columnCallbackFunctions);
                }
                modalloadAIUsage.hidePleaseWait();
            }
        });
    }

    $(document).ready(function() {
        loadAIUsage();
    });
</script>