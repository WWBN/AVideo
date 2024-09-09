<h2><?php echo __('MP3 Files details'); ?></h2>
<div class="list-group">
    <?php
    // Assuming $mp3s is your array variable
    foreach ($mp3s as $quality => $mp3Info) {
        if ($quality !== 'isValid' && $quality !== 'msg') { // Skip non-array entries
            $path = $mp3Info['paths']['path'];
            $url = $mp3Info['paths']['url'];
            $duration = $mp3Info['duration'];
            $isValid = $mp3Info['isValid'] ? 'Yes' : 'No';
            $fileSize = filesize($path); // Get file size in bytes

            // Convert file size to human-readable format
            $formattedSize = humanFileSize($fileSize);

            echo "<a href='{$url}' class='list-group-item' target='_blank'>";
            echo "<h4 class='list-group-item-heading'>File: " . basename($path) . "</h4>";
            echo "<p class='list-group-item-text'>Duration: $duration</p>";
            echo "<p class='list-group-item-text'>File Size: $formattedSize</p>";
            echo "<p class='list-group-item-text'>Valid: $isValid</p>";
            echo "</a>";
        }
    }
    ?>
</div>
<h2><?php echo __('Usage'); ?></h2>
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