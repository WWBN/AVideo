<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage(__("You cannot do this"));
    exit;
}
$_page = new Page(array(__('ADs Reports')));
$_page->setExtraScripts(array('node_modules/chart.js/dist/chart.umd.js', 'view/css/DataTables/datatables.min.js'));

$_page->setExtraStyles(
    array(
        'view/css/DataTables/datatables.min.css'
    )
);

$types = VastCampaignsLogs::getEventType();
$eventTypes = [];
foreach ($types as $key => $value) {
    $eventTypes[] = $value['type'];
}

?>
<div class="container-fluid">
    <style>
        .clickable-video {
            cursor: pointer;
            color: #007bff;
            text-decoration: underline;
        }
    </style>
    <div class="panel panel-default">
        <div class="panel-heading">
            <form id="report-form" class="text-center">
                <!-- Row for Date Range Selection -->
                <div class="row">
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label for="date-range" class="control-label"><?php echo __('Select Date Range'); ?></label>
                            <select id="date-range" class="form-control">
                                <optgroup label="<?php echo __('Preset Ranges'); ?>">
                                    <option value="thisWeek"><?php echo __('This Week'); ?></option>
                                    <option value="thisMonth"><?php echo __('This Month'); ?></option>
                                    <option value="last2Months"><?php echo __('Last 2 Months'); ?></option>
                                    <option value="thisYear"><?php echo __('This Year'); ?></option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label for="start-date" class="control-label"><?php echo __('Start Date'); ?></label>
                            <input type="date" id="start-date" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label for="end-date" class="control-label"><?php echo __('End Date'); ?></label>
                            <input type="date" id="end-date" class="form-control" required>
                        </div>
                    </div>
                </div>

                <!-- Row for Campaign Type and Event Type Selection -->
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="campaign-type" class="control-label"><?php echo __('Select Campaign Source'); ?></label>
                            <select id="campaign-type" class="form-control">
                                <optgroup label="<?php echo __('Campaign Type'); ?>">
                                    <option value="all"><?php echo __('All Campaigns'); ?></option>
                                    <option value="own"><?php echo __('Own Videos'); ?> (AD_Server)</option>
                                    <option value="third-party"><?php echo __('Third Party Ads'); ?> (GoogleAds_IMA)</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="event-type" class="control-label"><?php echo __('Select Event Type'); ?></label>
                            <select id="event-type" class="form-control">
                                <option value=""><?php echo __('All Event Types'); ?></option>
                                <?php foreach ($eventTypes as $eventType) : ?>
                                    <option value="<?= $eventType ?>"><?= $eventType ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Report Type Selection -->
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="report-type" class="control-label"><?php echo __('Select Report Type'); ?></label>
                            <select id="report-type" class="form-control" required>
                                <optgroup label="<?php echo __('Report Types'); ?>">
                                    <option value="adsByVideo"><?php echo __('Ads Per Video'); ?></option>
                                    <option value="adTypes"><?php echo __('Ad Types Overview'); ?></option>
                                    <option value="adsForSingleVideo"><?php echo __('Ads for a Single Video'); ?></option>
                                    <option value="adsByUser"><?php echo __('Ads by User'); ?></option>
                                    <option value="listVideosByUser"><?php echo __('List Videos and Total Ads by User'); ?></option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <!-- Video and User Select Containers (Hidden Initially) -->
                    <div class="col-md-3 col-sm-6" id="videos-select-container" style="display:none;">
                        <div class="form-group">
                            <label for="videos_id" class="control-label"><?php echo __('Select Video'); ?>:</label>
                            <?php echo Layout::getVideoAutocomplete(0, 'videos_id'); ?>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6" id="users-select-container" style="display:none;">
                        <div class="form-group">
                            <label for="users_id" class="control-label"><?php echo __('Select User'); ?>:</label>
                            <?php echo Layout::getUserAutocomplete(0, 'users_id'); ?>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Row -->
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary btn-block"><?php echo __('Generate Report'); ?></button>
                    </div>
                </div>
            </form>

        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <h4 id="selected-filters" class="text-center"></h4> <!-- Filter Info -->
                    <h4 id="total-sum" class="text-center"></h4> <!-- Total Sum -->
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <div class="chart-container" style="position: relative; width:100%; height:auto; max-height:400px;">
                <canvas id="reportChart"></canvas>
            </div>
            <table id="reportTable" class="table table-bordered table-responsive table-striped table-hover table-condensed" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th><?php echo __('Label'); ?></th>
                        <th><?php echo __('Total'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be populated dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const eventColors = {
        'AdStarted': '#00bfff', // Light Blue (Start of the ad)
        'AdFirstQuartile': '#4682b4', // Steel Blue (Progress through ad)
        'AdMidpoint': '#1e90ff', // Dodger Blue (Halfway through ad)
        'AdThirdQuartile': '#4169e1', // Royal Blue (Near completion of ad)
        'AdCompleted': '#32cd32', // Lime Green (Ad fully completed)
        'AdPaused': '#ffcc00', // Amber (Pausing the ad)
        'AdResumed': '#00ced1', // Dark Turquoise (Resuming the ad)
        'AdSkipped': '#ff8c00', // Dark Orange (Ad skipped)
        'AdClicked': '#00fa9a', // Medium Spring Green (Clicking on ad)
        'AdError': '#ff0000', // Red (Indicates an error)
        'AdMuted': '#b0c4de', // Light Steel Blue (Muted ad)
        'AdUnmuted': '#32cd32', // Lime Green (Unmuted ad)
        'AdRewind': '#800080', // Purple (Rewinding the ad)
        'AdFullscreen': '#4682b4', // Steel Blue (Entering fullscreen)
        'AdCreativeView': '#20b2aa', // Light Sea Green (Viewing creative ad)
        'AdExitFullscreen': '#ff6347', // Tomato (Exiting fullscreen)
        'AdAcceptInvitationLinear': '#ffd700', // Gold (Accepting linear ad invitation)
        'AdCloseLinear': '#696969' // Dim Grey (Closing linear ad)
    };



    function setVideoField(videos_id) {
        if (empty(videos_id)) {
            return false;
        }
        // Change the report type to "Ads for a Single Video"
        $('#report-type').val('adsForSingleVideo').trigger('change');
        $('#videos_id').val(videos_id);
        // Submit the form to regenerate the report
        $('#report-form').submit();
        <?php
        echo ($autoComplete);
        ?>
    }

    function displaySelectedFilters(reportType, startDate, endDate, eventType, campaignType) {
        var filterText = '';

        // Adding filter details in a compact format
        filterText += '<strong>Report Type:</strong> ' + $('#report-type option:selected').text() + '<br>';
        filterText += '<strong>Date Range:</strong> ' + startDate + ' to ' + endDate + '<br>';

        // Display Event Type only if it is not empty
        if (eventType) {
            filterText += '<strong>Event Type:</strong> ' + $('#event-type option:selected').text() + '<br>';
        }

        // Display Campaign Type only if it is not 'all'
        if (campaignType !== 'all') {
            filterText += '<strong>Campaign Type:</strong> ' + $('#campaign-type option:selected').text();
        }

        // Update the selected filters section with the constructed HTML
        $('#selected-filters').html(filterText);
    }

    function calculateTotalSum(data) {
        var totalSum = 0;

        // Calculate the total sum of ads
        data.forEach(function(item) {
            totalSum += parseInt(item.total_ads) || 0;
        });

        // Display the total sum with formatting
        $('#total-sum').html('<strong>Total Ads:</strong> ' + totalSum);
    }


    function createLabel(item) {
        var videoLabel = '';
        if (typeof item.video_title !== 'undefined' && !empty(item.video_title)) {
            videoLabel = '[' + item.videos_id + '] ' + item.video_title;
        } else if (typeof item.videos_id !== 'undefined' && !empty(item.videos_id)) {
            videoLabel = '[' + item.videos_id + ']';
        } else if (typeof item.campaign_name !== 'undefined' && !empty(item.campaign_name)) {
            videoLabel = item.campaign_name;
        }

        if (typeof item.type !== 'undefined' && !empty(item.type)) {
            if (empty(videoLabel)) {
                videoLabel = 'Google Ads IMA';
            }
            videoLabel = [item.type, videoLabel];
        } else {
            videoLabel = videoLabel.substring(0, 20);
        }
        return videoLabel;
    }

    $(document).ready(function() {
        // Initialize dates
        function setDefaultDates() {
            var today = new Date();
            var firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

            $('#start-date').val(firstDayOfMonth.toISOString().substr(0, 10));
            $('#end-date').val(today.toISOString().substr(0, 10));
        }

        setDefaultDates(); // Set default dates to the first day of this month and today

        // Handle date range selection
        $('#date-range').change(function() {
            var today = new Date();
            var startDate, endDate;

            switch ($(this).val()) {
                case 'thisMonth':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    endDate = today;
                    break;
                case 'thisWeek':
                    var firstDayOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
                    startDate = firstDayOfWeek;
                    endDate = new Date();
                    break;
                case 'last2Months':
                    startDate = new Date(today.getFullYear(), today.getMonth() - 2, 1);
                    endDate = today;
                    break;
                case 'thisYear':
                    startDate = new Date(today.getFullYear(), 0, 1);
                    endDate = today;
                    break;
                case 'custom':
                default:
                    return; // Do nothing for custom
            }

            $('#start-date').val(startDate.toISOString().substr(0, 10));
            $('#end-date').val(endDate.toISOString().substr(0, 10));
        });

        // Show/hide fields based on report type
        $('#report-type').change(function() {
            var reportType = $(this).val();
            if (reportType === 'adsByUser' || reportType === 'listVideosByUser') { // Handle both 'adsByUser' and 'listVideosByUser'
                $('#videos-select-container').hide();
                $('#users-select-container').show();
            } else if (reportType === 'adsForSingleVideo') {
                $('#videos-select-container').show();
                $('#users-select-container').hide();
            } else {
                $('#videos-select-container').hide();
                $('#users-select-container').hide();
            }
        });

        // Initialize DataTable
        var reportTable = $('#reportTable').DataTable({
            paging: true,
            searching: false,
            ordering: false
        });

        // Submit form to get report
        $('#report-form').submit(function(e) {
            e.preventDefault();

            var startDate = $('#start-date').val();
            var endDate = $('#end-date').val();
            var reportType = $('#report-type').val();
            var eventType = $('#event-type').val();
            var campaignType = $('#campaign-type').val();
            var usersId = $('#users_id').val();
            var videosId = 0;


            // Validate inputs based on report type
            if (reportType === 'adsForSingleVideo') {
                videosId = $('#videos_id').val();
                if ((!videosId || videosId < 1)) {
                    avideoAlertError('Please select a valid video');
                    return; // Stop form submission
                }
            }

            if ((reportType === 'adsByUser' || reportType === 'listVideosByUser') && (!usersId || usersId < 1)) {
                avideoAlertError('Please select a valid user');
                return; // Stop form submission
            }


            var requestData = {
                startDate: startDate,
                endDate: endDate,
                reportType: reportType,
                eventType: eventType,
                campaignType: campaignType,
                users_id: usersId,
                videos_id: videosId
            };
            // Display the selected filters
            displaySelectedFilters(reportType, startDate, endDate, eventType, campaignType);

            $.ajax({
                url: webSiteRootURL + 'plugin/AD_Server/reports.json.php', // Ensure this URL is correct and can handle the request
                method: 'POST',
                data: requestData,
                success: function(response) {
                    generateChart(response, reportType);
                    populateTable(response);
                    calculateTotalSum(response); // Calculate the total sum of ads
                }
            });
        });


        $('#report-form').submit();
        var reportChartInstance = null; // Variable to hold the chart instance

        function generateChart(data, reportType) {
            var ctx = document.getElementById('reportChart').getContext('2d');
            var chartType = 'bar';
            var labels = [];
            var values = [];
            var backgroundColors = [];
            var videoIds = []; // Array to store video IDs for corresponding columns

            // Check if there is an existing chart instance and destroy it before creating a new one
            if (reportChartInstance !== null) {
                reportChartInstance.destroy();
            }

            // Process the response data
            data.forEach(function(item, index) {
                var videoLabel = createLabel(item);
                labels.push(videoLabel);
                values.push(item.total_ads);
                videoIds.push(item.videos_id); // Store video IDs

                // Assign color based on event type, use default color if none found
                var eventType = item.type;
                var color = eventColors[eventType] || getRandomColor(); // Use event color or fallback to random
                backgroundColors.push(color);
            });

            // If there's no data, show a friendly message
            if (labels.length === 0) {
                labels.push("No data available");
                values.push(0);
                backgroundColors.push('#ddd');
            }

            // Create the new chart instance and assign it to the reportChartInstance variable
            reportChartInstance = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: __('Total Ads'),
                        data: values,
                        backgroundColor: backgroundColors,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Allows for flexible height
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false // This hides the top label (legend)
                        }
                    },
                    onClick: function(event, elements) {
                        if (elements.length > 0) {
                            // Get the index of the clicked bar
                            var clickedIndex = elements[0].index;
                            var selectedVideoId = videoIds[clickedIndex]; // Retrieve the corresponding video ID

                            // Set the #videos_id to the clicked video's ID
                            setVideoField(selectedVideoId);
                        }
                    },
                    hover: {
                        onHover: function(event, chartElement) {
                            event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                        }
                    }
                }
            });
        }


        function populateTable(data) {
            reportTable.clear();

            data.forEach(function(item) {
                var videoLabel = createLabel(item);
                var totalAds = item.total_ads;

                // Create a row and make the first column clickable if it's a video
                var row = $('<tr>');
                var videoCell = $('<td>').text(videoLabel);

                if (item.videos_id) {
                    videoCell.addClass('clickable-video').css('cursor', 'pointer').data('videosId', item.videos_id);
                }

                var totalCell = $('<td>').text(totalAds);
                row.append(videoCell).append(totalCell);
                reportTable.row.add(row);
            });

            reportTable.draw();

            // Attach click event to video cells
            $('.clickable-video').on('click', function() {
                var selectedVideoId = $(this).data('videosId');
                // Set the #videos_id to the clicked video's ID
                setVideoField(selectedVideoId);
            });
        }



        function getRandomColor() {
            var letters = '0123456789ABCDEF';
            var color = '#';
            for (var i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }
    });
</script>
<?php
$_page->print();
?>