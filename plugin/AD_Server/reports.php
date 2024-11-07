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

$referrers = VastCampaignsLogs::getExternalReferrer();
$referrersTypes = [];

foreach ($referrers as $key => $value) {
    if (!empty($value['external_referrer'])) {
        // Parse the URL to get the host (domain)
        $parsedUrl = parse_url($value['external_referrer'], PHP_URL_HOST);
        if ($parsedUrl) {
            // Remove 'www.' prefix if it exists
            $domain = preg_replace('/^www\./', '', $parsedUrl);
            $referrersTypes[] = $domain;
        }
    }
}

// Make sure the $referrersTypes is unique and sorted alphabetically
$referrersTypes = array_unique($referrersTypes);
sort($referrersTypes);



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

            <form id="report-form">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Row for Date Range Selection -->
                        <div class="row">
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label for="date-range" class="control-label"><?php echo __('Date Range'); ?></label>
                                    <select id="date-range" class="form-control">
                                        <optgroup label="<?php echo __('Preset Ranges'); ?>">
                                            <option value="thisWeek"><?php echo __('This Week'); ?></option>
                                            <option value="thisMonth"><?php echo __('This Month'); ?></option>
                                            <option value="lastMonth"><?php echo __('Last Month'); ?></option>
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
                            <div class="col-md-2 col-sm-6">
                                <div class="form-group">
                                    <label for="campaign-type" class="control-label"><?php echo __('Campaign Source'); ?></label>
                                    <select id="campaign-type" class="form-control">
                                        <optgroup label="<?php echo __('Campaign Type'); ?>">
                                            <option value="all"><?php echo __('All Campaigns'); ?></option>
                                            <option value="own"><?php echo __('Own Videos'); ?> (AD_Server)</option>
                                            <option value="third-party"><?php echo __('Third Party Ads'); ?> (GoogleAds_IMA)</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-6">
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

                            <div class="col-md-2 col-sm-6">
                                <div class="form-group">
                                    <label for="referrer-type" class="control-label"><?php echo __('Referrer'); ?></label>
                                    <select id="referrer-type" class="form-control">
                                        <option value=""><?php echo __('All Referrers'); ?></option>
                                        <?php foreach ($referrersTypes as $referrer) : ?>
                                            <option value="<?= $referrer ?>"><?= $referrer ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Report Type Selection -->
                            <div class="col-md-2 col-sm-6">
                                <div class="form-group">
                                    <label for="report-type" class="control-label"><?php echo __('Report Type'); ?></label>
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
                            <div class="col-md-4 col-sm-6" id="videos-select-container" style="display:none;">
                                <div class="form-group">
                                    <label for="videos_id" class="control-label"><?php echo __('Video'); ?>:</label>
                                    <?php $autoComplete = Layout::getVideoAutocomplete(0, 'videos_id'); ?>
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-6" id="users-select-container" style="display:none;">
                                <div class="form-group">
                                    <label for="users_id" class="control-label"><?php echo __('User'); ?>:</label>
                                    <?php $updateUserAutocomplete = Layout::getUserAutocomplete(0, 'users_id'); ?>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 id="selected-filters"></h4> <!-- Filter Info -->
                            </div>
                            <div class="panel-body">
                                <h2 id="total-sum"></h2> <!-- Total Sum -->
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-3 text-center">
                        <?php
                        echo getTourHelpButton('plugin/AD_Server/reports.help.json', 'btn btn-default btn-block');
                        ?>
                    </div>
                    <div class="col-md-9 text-center">
                        <button type="submit" class="btn btn-primary btn-block" id="genReports"><i class="fas fa-chart-bar"></i> <?php echo __('Generate Report'); ?></button>
                    </div>
                </div>
            </form>

        </div>
        <div class="panel-body">
            <div class="chart-container" style="position: relative; width:100%; height:auto; height:300px;">
                <canvas id="reportChart"></canvas>
            </div>
            <div class="clearfix"></div>
            <div class="chart-container" style="position: relative; width:100%; height:auto; height:300px;">
                <canvas id="pieChart"></canvas> <!-- New pie chart -->
            </div>
            <div class="clearfix"></div>
            <table id="reportTable" class="table table-bordered table-responsive table-striped table-hover table-condensed" style="margin-top: 40px;">
                <thead>
                    <tr>
                        <th><?php echo __('Label'); ?></th>
                        <th><?php echo __('Total'); ?></th>
                        <th><?php echo __('User'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be populated dynamically -->
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <!-- Add a button to download CSV -->
            <button id="download-csv-btn" class="btn btn-success btn-block"><i class="fas fa-file-csv"></i> <?php echo __('Download CSV'); ?></button>
        </div>
    </div>
</div>

<script>
    const eventColors = {
        'AdStarted': 'rgba(0, 191, 255, 0.5)', // Light Blue (Start of the ad)
        'AdFirstQuartile': 'rgba(70, 130, 180, 0.5)', // Steel Blue (Progress through ad)
        'AdMidpoint': 'rgba(30, 144, 255, 0.5)', // Dodger Blue (Halfway through ad)
        'AdThirdQuartile': 'rgba(65, 105, 225, 0.5)', // Royal Blue (Near completion of ad)
        'AdCompleted': 'rgba(50, 205, 50, 0.5)', // Lime Green (Ad fully completed)
        'AdPaused': 'rgba(255, 204, 0, 0.5)', // Amber (Pausing the ad)
        'AdResumed': 'rgba(0, 206, 209, 0.5)', // Dark Turquoise (Resuming the ad)
        'AdSkipped': 'rgba(255, 140, 0, 0.5)', // Dark Orange (Ad skipped)
        'AdClicked': 'rgba(0, 250, 154, 0.5)', // Medium Spring Green (Clicking on ad)
        'AdError': 'rgba(255, 0, 0, 0.5)', // Red (Indicates an error)
        'AdMuted': 'rgba(176, 196, 222, 0.5)', // Light Steel Blue (Muted ad)
        'AdUnmuted': 'rgba(50, 205, 50, 0.5)', // Lime Green (Unmuted ad)
        'AdRewind': 'rgba(128, 0, 128, 0.5)', // Purple (Rewinding the ad)
        'AdFullscreen': 'rgba(70, 130, 180, 0.5)', // Steel Blue (Entering fullscreen)
        'AdCreativeView': 'rgba(32, 178, 170, 0.5)', // Light Sea Green (Viewing creative ad)
        'AdExitFullscreen': 'rgba(255, 99, 71, 0.5)', // Tomato (Exiting fullscreen)
        'AdAcceptInvitationLinear': 'rgba(255, 215, 0, 0.5)', // Gold (Accepting linear ad invitation)
        'AdCloseLinear': 'rgba(105, 105, 105, 0.5)' // Dim Grey (Closing linear ad)
    };

    var colorCache = {}; // Cache for dynamically generated colors
    var reportChartInstance = null;
    var pieChartInstance = null;
    var reportTable; // Declare reportTable variable globally
    var lastReportTableData;


    function getColorForElement(type) {
        // Check if the type already has a predefined color
        if (eventColors[type]) {
            return eventColors[type];
        }

        // If the type is not in the predefined list, check the cache
        if (colorCache[type]) {
            return colorCache[type];
        }

        // Generate a random color and store it in the cache
        var newColor = getRandomColor();
        colorCache[type] = newColor;
        return newColor;
    }

    function jsonToCSV(jsonData) {
        const csvRows = [];

        // Add filter information as the first rows in the CSV
        const filters = {
            'Report Type': $('#report-type option:selected').text(),
            'Date Range': `${$('#start-date').val()} to ${$('#end-date').val()}`,
            'Event Type': $('#event-type option:selected').text() || 'All Event Types',
            'Referrer Type': $('#referrer-type option:selected').text() || 'All Referrers',
            'Campaign Type': $('#campaign-type option:selected').text() || 'All Campaigns'
        };

        // Add each filter as a separate line
        for (const [key, value] of Object.entries(filters)) {
            csvRows.push(`${key},${value}`);
        }

        csvRows.push(''); // Add a blank line for separation

        // Add headers from the first element of the JSON data
        const headers = Object.keys(jsonData[0]);
        csvRows.push(headers.join(',')); // Headers row

        // Convert each JSON row to CSV format
        jsonData.forEach(item => {
            const values = headers.map(header => {
                const escapeValue = item[header] == null ? '' : item[header].toString().replace(/"/g, '""');
                return `"${escapeValue}"`;
            });
            csvRows.push(values.join(','));
        });

        return csvRows.join('\n');
    }


    function downloadCSV(csvContent) {
        // Generate a filename based on the report type and date range
        const reportType = $('#report-type option:selected').text().replace(/\s+/g, '_');
        const startDate = $('#start-date').val();
        const endDate = $('#end-date').val();
        const filename = `report_${reportType}_${startDate}_to_${endDate}.csv`;

        // Create and trigger the CSV download
        const blob = new Blob([csvContent], {
            type: 'text/csv'
        });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }


    function setVideoField(videos_id) {
        if (empty(videos_id)) {
            return false;
        }
        if ($('#videos_id').val() == videos_id) {
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

    function setEventType(selectedEventType) {
        if (empty(selectedEventType)) {
            return false;
        }
        if ($('#report-type').val() === 'adsByVideo') {
            return false;
        }
        $('#report-type').val('adsByVideo');
        // Automatically set the #event-type dropdown to the clicked event type
        $('#event-type').val(selectedEventType);

        // Trigger the form submission
        $('#report-form').submit();
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
        var videoLabel = [];
        if (typeof item.video_title !== 'undefined' && !empty(item.video_title)) {
            videoLabel.push('[' + item.videos_id + '] ' + item.video_title);
        } else if (typeof item.videos_id !== 'undefined' && !empty(item.videos_id)) {
            videoLabel.push('[' + item.videos_id + ']');
        }

        if (typeof item.channelName !== 'undefined' && !empty(item.channelName)) {
            videoLabel.push('[' + item.users_id + '] ' + item.channelName);
        }

        if (typeof item.campaign_name !== 'undefined' && !empty(item.campaign_name)) {
            videoLabel.push(item.campaign_name);
            videoLabel.push(item.type);
        } else if (typeof item.type !== 'undefined' && !empty(item.type)) {
            if (empty(videoLabel)) {
                videoLabel.push('Google Ads IMA');
            }
            videoLabel.push(item.type);
        }

        videoLabel = videoLabel.map(function(label) {
            if (typeof label == 'undefined') {
                return '';
            }
            return label.length > 40 ? label.substring(0, 20) + '...' : label;
        });
        return videoLabel;
    }

    // Function to handle the user click event and trigger listVideosByUser report
    function setUserField(users_id) {
        if (empty(users_id)) {
            return false;
        }
        // Change the report type to "List Videos and Total Ads by User"
        $('#report-type').val('listVideosByUser').trigger('change');
        $('#users_id').val(users_id);
        // Submit the form to regenerate the report for the selected user
        $('#report-form').submit();
        <?php
        echo ($updateUserAutocomplete);
        ?>
    }

    function getRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        // Convert the hex color to an RGBA string with 50% transparency
        var r = parseInt(color.slice(1, 3), 16);
        var g = parseInt(color.slice(3, 5), 16);
        var b = parseInt(color.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, 0.5)`; // 50% transparent color
    }

    // Function to set default dates
    function setDefaultDates() {
        var today = new Date();
        var firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        $('#start-date').val(firstDayOfMonth.toISOString().substr(0, 10));
        $('#end-date').val(today.toISOString().substr(0, 10));
    }

    // Function to handle date range changes
    function handleDateRangeChange() {
        var today = new Date();
        var startDate, endDate;

        switch ($('#date-range').val()) {
            case 'thisMonth':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = today;
                break;
            case 'thisWeek':
                var firstDayOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
                startDate = firstDayOfWeek;
                endDate = new Date();
                break;
            case 'lastMonth':
                startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                endDate = new Date(today.getFullYear(), today.getMonth(), 0); // Last day of the previous month
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
    }

    // Function to handle report type changes
    function handleReportTypeChange() {
        var reportType = $('#report-type').val();
        if (reportType === 'adsByUser' || reportType === 'listVideosByUser') {
            $('#videos-select-container').hide();
            $('#users-select-container').show();
        } else if (reportType === 'adsForSingleVideo') {
            $('#videos-select-container').show();
            $('#users-select-container').hide();
        } else {
            $('#videos-select-container').hide();
            $('#users-select-container').hide();
        }
    }

    // Function to display selected filters
    function displaySelectedFilters(reportType, startDate, endDate, eventType, campaignType, referrerType) {
        var filterText = '';

        filterText += '<strong>Report Type:</strong> ' + $('#report-type option:selected').text() + '<br>';
        filterText += '<strong>Date Range:</strong> ' + startDate + ' to ' + endDate + '<br>';

        if (eventType) {
            filterText += '<strong>Event Type:</strong> ' + $('#event-type option:selected').text() + '<br>';
        }

        if (referrerType) {
            filterText += '<strong>Referrer Type:</strong> ' + $('#referrer-type option:selected').text() + '<br>';
        }

        if (campaignType !== 'all') {
            filterText += '<strong>Campaign Type:</strong> ' + $('#campaign-type option:selected').text();
        }

        $('#selected-filters').html(filterText);
    }

    // Function to populate the DataTable with new data
    function populateTable(data) {
        // Clear the table and remove any lingering data
        reportTable.clear().draw(); // Clear the DataTable before adding new data

        console.log('populateTable', data); // Log the data to check it's new data
        lastReportTableData = data;
        // Loop through the new data and add it to the table
        data.forEach(function(item) {
            var videoLabel = createLabel(item);
            var totalAds = item.total_ads;
            var userCellContent = '';

            // Generate the user link or display "Unknown User"
            if (item.users_id && item.channelName) {
                userCellContent = `<a href="#" class="clickable-user" data-users-id="${item.users_id}">[${item.users_id}] ${item.channelName}</a>`;
            } else if (item.users_id) {
                userCellContent = `<a href="#" class="clickable-user" data-users-id="${item.users_id}">[${item.users_id}]</a>`;
            } else {
                userCellContent = ' -- ';
            }

            // Create a row element for the table
            var row = $('<tr>');

            // Create the video cell with fallback for no video title
            var videoCell = $('<td>').html(createLabel(item).join('<br>'));
            if (item.videos_id) {
                videoCell.addClass('clickable-video').css('cursor', 'pointer').data('videosId', item.videos_id);
            }

            // Create the total ads cell
            var totalCell = $('<td>').text(totalAds);

            // Create the user cell with the clickable content
            var userCell = $('<td>').html(userCellContent);

            // Append the cells to the row
            row.append(videoCell).append(totalCell).append(userCell);

            // Add the row to the DataTable
            reportTable.row.add(row); // No need to call [0], just use the jQuery object
        });

        // Redraw the table to show the new data
        reportTable.draw();

        // Reattach click events to new clickable elements after rows are added
        $('.clickable-video').on('click', function() {
            var selectedVideoId = $(this).data('videosId');
            setVideoField(selectedVideoId); // Trigger Ads for the selected video
        });

        $('.clickable-user').on('click', function(e) {
            e.preventDefault();
            var selectedUserId = $(this).data('users-id');
            setUserField(selectedUserId); // Trigger the listVideosByUser report
        });
    }

    // Function to generate the bar chart
    var hiddenItems = {}; // Track hidden items by label

    var hiddenItems = {}; // Track hidden items by label

    // Function to generate the bar chart with support for hiding/showing elements
    function generateChart(data, reportType, hiddenItems = {}) {
        var ctx = document.getElementById('reportChart').getContext('2d');
        var chartType = 'bar';
        var labels = [];
        var values = [];
        var backgroundColors = [];
        var borderColors = [];
        var videoIds = [];

        if (reportChartInstance !== null && typeof reportChartInstance !== 'undefined') {
            reportChartInstance.destroy();
        }

        data.forEach(function(item) {
            var videoLabel = createLabel(item);
            var label = videoLabel.join(' ');

            // Skip items that are currently hidden
            if (hiddenItems[label]) return;

            labels.push(videoLabel);
            values.push(item.total_ads);
            videoIds.push(item.videos_id);

            var baseColor = getColorForElement(empty(item.type) ? videoLabel.join('') : item.type); // Get consistent color for the element
            backgroundColors.push(baseColor.replace('1)', '0.5)')); // Set background color to 50% transparent
            borderColors.push(baseColor.replace('0.5)', '1)')); // Set border color as solid
        });

        if (labels.length === 0) {
            labels.push("No data available");
            values.push(0);
            backgroundColors.push('rgba(0, 0, 0, 0.5)');
            borderColors.push('rgba(0, 0, 0, 1)');
        }

        reportChartInstance = new Chart(ctx, {
            type: chartType,
            data: {
                labels: labels,
                datasets: [{
                    label: __('Total Ads'),
                    data: values,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 2 // Set border thickness
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        //type: 'logarithmic', // Use logarithmic scale
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                if (Number.isInteger(value)) {
                                    return value; // Show only integer numbers
                                }
                                return null; // Skip non-integer numbers
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 11 // Optional: Reduce font size
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Function to generate the pie chart with hide/show functionality based on legend clicks
    function generatePieChart(data) {
        var ctxPie = document.getElementById('pieChart').getContext('2d');
        var pieLabels = [];
        var pieValues = [];
        var pieColors = [];
        var pieBorderColors = [];
        var othersTotal = 0;
        var percentageThreshold = 0.01; // Set the percentage threshold (e.g., 1% of the largest value)

        // Get the largest value in the dataset
        var maxValue = Math.max(...data.map(item => item.total_ads));

        data.forEach(function(item) {
            var videoLabel = createLabel(item);
            var label = videoLabel.join(' ');

            // Skip hidden items
            if (hiddenItems[label]) return;

            if (item.total_ads >= maxValue * percentageThreshold) {
                pieLabels.push(label);
                pieValues.push(item.total_ads);
                var baseColor = getColorForElement(empty(item.type) ? videoLabel.join('') : item.type); // Get consistent color for the element
                pieColors.push(baseColor.replace('1)', '0.5)')); // Set background color to 50% transparent
                pieBorderColors.push(baseColor.replace('0.5)', '1)')); // Set border color as solid
            } else {
                othersTotal += item.total_ads;
            }
        });

        if (othersTotal > 0) {
            pieLabels.push('Others');
            pieValues.push(othersTotal);
            pieColors.push('rgba(153, 153, 153, 0.5)'); // Default color for "Others"
            pieBorderColors.push('rgba(153, 153, 153, 1)'); // Solid border for "Others"
        }

        if (pieChartInstance !== null && typeof pieChartInstance !== 'undefined') {
            pieChartInstance.destroy();
        }

        pieChartInstance = new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: pieLabels,
                datasets: [{
                    data: pieValues,
                    backgroundColor: pieColors,
                    borderColor: pieBorderColors,
                    borderWidth: 2 // Set border thickness
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right', // Set the position to the right
                        labels: {
                            boxWidth: 20, // Customize the width of the box
                            padding: 20 // Customize padding between legend items
                        },
                        onClick: function(e, legendItem) {
                            var index = legendItem.index;
                            pieChartInstance.toggleDataVisibility(index); // toggles the item in all datasets, at index 2
                            pieChartInstance.update();

                            reportChartInstance.toggleDataVisibility(index); // toggles the item in all datasets, at index 2
                            reportChartInstance.update();
                        }
                    }
                }
            }
        });
    }

    $(document).ready(function() {
        reportTable = $('#reportTable').DataTable({
            paging: true,
            searching: false,
            ordering: false
        });

        // Set default dates when the page loads
        setDefaultDates();

        // Handle date range selection
        $('#date-range').change(function() {
            handleDateRangeChange();
        });

        // Show/hide fields based on report type
        $('#report-type').change(function() {
            handleReportTypeChange();
        });

        // Submit form to get report
        $('#report-form').submit(function(e) {
            e.preventDefault();

            var startDate = $('#start-date').val();
            var endDate = $('#end-date').val();
            var reportType = $('#report-type').val();
            var eventType = $('#event-type').val();
            var referrerType = $('#referrer-type').val();
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
                referrerType: referrerType,
                campaignType: campaignType,
                users_id: usersId,
                videos_id: videosId
            };

            // Display the selected filters
            displaySelectedFilters(reportType, startDate, endDate, eventType, campaignType, referrerType);

            $.ajax({
                url: webSiteRootURL + 'plugin/AD_Server/reports.json.php', // Ensure this URL is correct and can handle the request
                method: 'POST',
                data: requestData,
                success: function(response) {
                    generateChart(response, reportType, hiddenItems); // Pass hidden items to the bar chart
                    // Generate Pie Chart
                    generatePieChart(response);

                    populateTable(response);
                    calculateTotalSum(response); // Calculate the total sum of ads

                    // Enable the download button with the JSON response data
                    $('#download-csv-btn').off('click').on('click', function() {
                        const csvContent = jsonToCSV(response); // Convert the JSON response to CSV
                        downloadCSV(csvContent); // Trigger the CSV download
                    });
                }
            });
        });

        // Trigger the form submission when the page loads
        $('#report-form').submit();
    });
</script>
<?php
$_page->print();
?>