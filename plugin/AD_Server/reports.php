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

    <div class="panel panel-default">
        <div class="panel-heading">
            <form id="report-form" class="text-center">
                <!-- Row for Date Range Selection -->
                <div class="row">
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label for="date-range" class="control-label"><?php echo __('Select Range'); ?></label>
                            <select id="date-range" class="form-control">
                                <option value="custom"><?php echo __('Custom'); ?></option>
                                <option value="thisMonth"><?php echo __('This Month'); ?></option>
                                <option value="thisWeek"><?php echo __('This Week'); ?></option>
                                <option value="last2Months"><?php echo __('Last 2 Months'); ?></option>
                                <option value="thisYear"><?php echo __('This Year'); ?></option>
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
                <!-- Row for Campaign Type Selection -->
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="campaign-type" class="control-label"><?php echo __('Campaign Source'); ?></label>
                            <select id="campaign-type" class="form-control">
                                <option value="all"><?php echo __('All Campaigns'); ?></option>
                                <option value="own">AD_server</option>
                                <option value="third-party">GoogleAds_IMA</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="event-type" class="control-label"><?php echo __('Event Type'); ?></label>
                            <select id="event-type" class="form-control">
                                <option value=""><?php echo __('All Event Types'); ?></option>
                                <?php foreach ($eventTypes as $eventType) : ?>
                                    <option value="<?= $eventType ?>"><?= $eventType ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="report-type" class="control-label"><?php echo __('Report Type'); ?></label>
                            <select id="report-type" class="form-control" required>
                                <option value="adsByVideo"><?php echo __('Ads Per Video'); ?></option>
                                <option value="adTypes"><?php echo __('Ad Types Overview'); ?></option>
                                <option value="adsForSingleVideo"><?php echo __('Ads for a Single Video'); ?></option>
                                <option value="adsByUser"><?php echo __('Ads by User'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6" id="videos-select-container" style="display:none;">
                        <div class="form-group">
                            <label for="videos_id" class="control-label"><?php echo __('Select Video'); ?>:</label>
                            <?php
                            $autoComplete = Layout::getVideoAutocomplete(0, 'videos_id');
                            ?>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6" id="users-select-container" style="display:none;">
                        <div class="form-group">
                            <label for="users_id" class="control-label"><?php echo __('User'); ?>:</label>
                            <?php
                            $updateUserAutocomplete = Layout::getUserAutocomplete(0, 'users_id');
                            ?>
                        </div>
                    </div>
                </div>
                <!-- Row for Submit Button -->
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary btn-block"><?php echo __('Generate Report'); ?></button>
                    </div>
                </div>
            </form>


        </div>
        <div class="panel-body">
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
            if (reportType === 'adsForSingleVideo') {
                $('#videos-select-container').show();
                $('#users-select-container').hide();
            } else if (reportType === 'adsByUser') {
                $('#videos-select-container').hide();
                $('#users-select-container').show();
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
            var eventType = $('#event-type').val(); // Get selected event type
            var campaignType = $('#campaign-type').val(); // Get selected campaign type
            var videosId = $('#videos_id').val();
            var usersId = $('#users_id').val();

            var requestData = {
                startDate: startDate,
                endDate: endDate,
                reportType: reportType,
                eventType: eventType, // Include the event type in the request
                campaignType: campaignType // Include the campaign type in the request
            };

            if (reportType === 'adsForSingleVideo') {
                requestData.videos_id = videosId;
            } else if (reportType === 'adsByUser') {
                requestData.users_id = usersId;
            }

            $.ajax({
                url: webSiteRootURL + 'plugin/AD_Server/reports.json.php',
                method: 'POST',
                data: requestData,
                success: function(response) {
                    generateChart(response, reportType);
                    populateTable(response);
                }
            });
        });


        var reportChartInstance = null; // Variable to hold the chart instance

        function generateChart(data, reportType) {
            var ctx = document.getElementById('reportChart').getContext('2d');
            var chartType = 'bar';
            var labels = [];
            var values = [];
            var backgroundColors = [];

            // Log the response data for debugging
            console.log("Response Data:", data);

            // Check if there is an existing chart instance and destroy it before creating a new one
            if (reportChartInstance !== null) {
                reportChartInstance.destroy();
            }

            // Process the response based on report type
            data.forEach(function(item, index) {
                labels.push(item.type || 'Video ' + item.videos_id);
                values.push(item.total_ads);
                backgroundColors.push(getRandomColor());
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
                        label: __('Total'),
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
                    }
                }
            });

        }

        function populateTable(data) {
            reportTable.clear();
            data.forEach(function(item) {
                reportTable.row.add([item.type || 'Video ' + item.videos_id, item.total_ads]);
            });
            reportTable.draw();
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