<?php
require_once '../../videos/configuration.php';

if (!User::isAdmin()) {
    forbiddenPage('You must be Admin');
}

if (!AVideoPlugin::isEnabledByName('AD_Server')) {
    forbiddenPage('AD_Server is disabled');
}

function generateCSV($vast_campaigns_id, $start_date, $end_date)
{
    // Fetch the campaign logs
    $logs = VastCampaignsLogs::getCampaignLogs($vast_campaigns_id, $start_date, $end_date);

    if (empty($logs)) {
        _error_log("No data available for the specified period and campaign. $vast_campaigns_id, $start_date, $end_date");
        echo '<script>$(document).ready(function () {avideoToastError("No data available for the specified period and campaign")});</script>';
        return false;
    }

    // Get the campaign name and sanitize it
    $campaign = new VastCampaigns($vast_campaigns_id);
    $name = $campaign->getName();
    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);

    // Set the headers for the CSV file download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="campaign_logs_' . $vast_campaigns_id . '_' . $safeName . '.csv"');

    // Open the output stream
    $output = fopen('php://output', 'w');

    // Write the header row
    fputcsv($output, ['Ad Start Time', 'Ad Video ID', 'Ad Played On Video', 'Publisher Channel', 'Viewer ID', 'Viewer Name', 'Viewer IP', 'Viewer Browser', 'Viewer Operating System', 'Viewer Device']);

    // Write the data rows
    foreach ($logs as $log) {

        // Fetch the video details
        $video = new Video('', '', $log['videos_id']);
        $video_owners_users_id = $video->getUsers_id();
        $video_owner_name = User::getNameIdentificationById($video_owners_users_id);

        // Fetch the user/client details
        $client_name = User::getNameIdentificationById($log['users_id']);
        $browser = get_browser_name($log['user_agent']);
        $os = getOS($log['user_agent']);
        $device = isMobile($log['user_agent']) ? "Mobile" : "PC";

        fputcsv($output, [
            $log['created'], // Ad start time
            $log['campaign_videos_id'], // Ad video ID
            $log['videos_id'],
            $video_owner_name, // Video played on
            $log['users_id'], // Client ID
            $client_name, // Client name
            $log['ip'], // Client IP
            $browser, // Browser
            $os, // Operating System
            $device // Device type
        ]);
    }

    // Close the output stream
    fclose($output);
    return true;
}


// Get the current date
$today = date('Y-m-d 00:00:00');
$end_of_today = date('Y-m-d 23:59:59');

// Parameters passed via GET or POST request (ensure they are sanitized in real-world applications)
$vast_campaigns_id = isset($_REQUEST['vast_campaigns_id']) ? intval($_REQUEST['vast_campaigns_id']) : 0;
$start_date = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : $today;
$end_date = isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : $end_of_today;
$success = false;
if (!empty($_REQUEST['downloadNow'])) {
    $success = generateCSV($vast_campaigns_id, $start_date, $end_date);
}

if (!$success) {
    $_page = new Page(array('Download Campaign Logs'));
?>
    <div class="container">
        <form action="#" method="GET" class="form-horizontal">
            <input type="hidden" name="downloadNow" value="1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2>Download Campaign Logs</h2>
                    <p>Select the campaign, start date, and end date to download the log as a CSV file.</p>
                </div>
                <div class="panel-body">

                    <div class="form-group">
                        <label for="vast_campaigns_id" class="col-sm-2 control-label">Campaign <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="Select the campaign you want to download logs for."></i></label>
                        <div class="col-sm-10">
                            <select class="form-control" id="vast_campaigns_id" name="vast_campaigns_id" required>
                                <option value="" disabled selected>Select a campaign</option>
                                <?php
                                $rows = VastCampaigns::getAll();
                                foreach ($rows as $key => $row) {
                                    $selected = '';
                                    if ($_REQUEST['vast_campaigns_id'] == $row['id']) {
                                        $selected = 'selected';
                                    }
                                    echo "<option value=\"{$row['id']}\" {$selected}>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="start_date" class="col-sm-2 control-label">Start Date <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="Select the start date for the log period."></i></label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" id="start_date" name="start_date" placeholder="YYYY-MM-DD" value="<?php echo date('Y-m-d', strtotime($start_date)); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="end_date" class="col-sm-2 control-label">End Date <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="Select the end date for the log period."></i></label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" id="end_date" name="end_date" placeholder="YYYY-MM-DD" value="<?php echo date('Y-m-d', strtotime($end_date)); ?>" required>
                        </div>
                    </div>

                </div>
                <div class="panel-footer">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-download"></i> Download CSV</button>
                </div>
            </div>
        </form>

    </div>

<?php
    $_page->print();
}
?>