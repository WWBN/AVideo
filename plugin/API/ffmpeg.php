<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage('Admin only');
}

require_once $global['systemRootPath'] . 'plugin/API/API.php';
$plugin = AVideoPlugin::loadPluginIfEnabled("API");
if (empty($plugin)) {
    forbiddenPage('API Plugin disabled');
}
$obj = AVideoPlugin::getObjectData("API");


$_page = new Page(array('FFMPEG'));

?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('FFmpeg Process Manager'); ?> </div>
        <div class="panel-body">

            <table class="table table-bordered table-striped" id="processTable">
                <thead>
                    <tr>
                        <th>PID</th>
                        <th>Command</th>
                        <th>CPU (%)</th>
                        <th>Memory (%)</th>
                        <th>Running Time</th>
                        <th>Running Time (s)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded here -->
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <button id="refreshBtn" class="btn btn-primary btn-block"><?php echo __('Refresh List'); ?></button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var loadProcessesModal = getPleaseWait();

        function loadProcesses() {
            loadProcessesModal.showPleaseWait();

            $.ajax({
                url: webSiteRootURL + "plugin/API/list.ffmpeg.json.php",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    let tableBody = $("#processTable tbody");
                    tableBody.empty();

                    if (data.error || !data.list || data.list.length === 0) {
                        tableBody.append("<tr><td colspan='7' class='text-center'>No FFmpeg processes running</td></tr>");
                        return;
                    }

                    $.each(data.list, function(index, process) {
                        let row = `<tr>
                            <td>${process.pid}</td>
                            <td style="word-break: break-word;">${process.command}</td>
                            <td>${process.cpu_usage}</td>
                            <td>${process.memory_usage}</td>
                            <td>${process.running_time}</td>
                            <td>${process.running_time_seconds}</td>
                            <td>
                                <button class="btn btn-danger btn-sm kill-btn btn-block" data-pid="${process.pid}">Kill</button>
                            </td>
                        </tr>`;
                        tableBody.append(row);
                    });
                    loadProcessesModal.hidePleaseWait();
                },
                complete: function(resp) {
                    response = resp.responseJSON
                    avideoResponse(response);
                    loadProcessesModal.hidePleaseWait();
                }
            });

        }


        // Load process list on page load
        loadProcesses();

        // Refresh button
        $("#refreshBtn").click(function() {
            loadProcesses();
        });

        // Handle process kill button click
        $(document).on("click", ".kill-btn", function() {
            let pid = $(this).data("pid");

            avideoConfirm("Are you sure you want to kill process " + pid + "?").then(response => {
                if (response) {
                    modal.showPleaseWait();

                    $.ajax({
                        url: webSiteRootURL + "plugin/API/kill.ffmpeg.json.php",
                        type: "POST",
                        data: {
                            pid: pid
                        },
                        dataType: "json",
                        success: function(response) {
                            avideoResponse(response);
                            loadProcesses(); // Refresh the list after a successful kill
                            modal.hidePleaseWait();
                        },
                        complete: function(resp) {
                            response = resp.responseJSON
                            avideoResponse(response);
                            modal.hidePleaseWait();
                        }
                    });

                } else {
                    return false;
                }
            });
        });
    });
</script>
<?php
$_page->print();
?>
