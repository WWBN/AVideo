<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

if (!User::isAdmin()) {
    forbiddenPage('You cannot do this');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

// Function to fetch FFMPEG processes
function getFfmpegProcesses()
{
    $output = [];
    exec("/bin/ps aux | grep ffmpeg | grep -v grep", $output);
    _error_log(print_r($output, true)); // Log the output for debugging
    $processes = [];

    foreach ($output as $line) {
        $parts = preg_split('/\\s+/', $line, 11);
        if (count($parts) >= 11) {
            $processes[] = [
                'user' => $parts[0],
                'pid' => $parts[1],
                'pidEncrypted' => encryptString($parts[1]),
                'cpu' => $parts[2],
                'mem' => $parts[3],
                'runtime' => $parts[9], // Extracts the elapsed time (e.g., "00:05:32")
                'command' => $parts[10],
            ];
        }
    }

    return $processes;
}


// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    header('Content-Type: application/json');
    echo json_encode(getFfmpegProcesses());
    exit;
}

// Handle kill process request securely
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pid'], $_POST['csrf_token'])) {
    if (hash_equals($csrfToken, $_POST['csrf_token'])) {
        $pidDecrypted = decryptString($_POST['pid']);
        $pid = intval($pidDecrypted);
        if ($pid > 0) {
            $command = "kill -9 $pid";
            $output = [];
            $return_var = 0;

            // Execute the command and capture the output and return status
            exec($command . " 2>&1", $output, $return_var);

            // Prepare the response JSON with detailed information
            echo json_encode([
                'error' => $return_var !== 0,
                'msg' => $return_var === 0 ? 'Process killed successfully.' : 'Failed to kill the process.',
                'command' => $command,
                'output' => $output
            ]);
            exit;
        }
    }

    echo json_encode(['error' => true, 'msg' => 'Invalid request.']);
    exit;
}

?>
<script src="<?php echo getURL('node_modules/chart.js/dist/chart.umd.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('view/css/DataTables/datatables.min.js'); ?>" type="text/javascript"></script>
<link href="<?php echo getURL('view/css/DataTables/datatables.min.css'); ?>" rel="stylesheet" type="text/css" />

<div class="container-fluid">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h1 class="text-center">FFmpeg Monitor</h1>
        </div>

        <div class="panel-body">
            <!-- Doughnut Charts for Overall Usage -->
            <div class="row">
                <div class="col-md-2">
                    <h4>CPU Usage</h4>
                    <canvas id="cpuDoughnut" style="width: 100%; height: 250px;"></canvas>
                </div>
                <div class="col-md-2">
                    <h4>RAM Usage</h4>
                    <canvas id="ramDoughnut" style="width: 100%; height: 250px;"></canvas>
                </div>
                <div class="col-md-8">
                    <h4>Historical Resource Usage</h4>
                    <canvas id="historicalLine" style="width: 100%; height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <!-- Process Table -->
            <table id="processTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>PID</th>
                        <th>CPU%</th>
                        <th>MEM%</th>
                        <th>Runtime</th> <!-- New Column -->
                        <th>Command</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const csrfToken = '<?= $csrfToken; ?>';
        const historicalCPU = [];
        const historicalRAM = [];
        const processNames = [];
        const processCPU = [];
        const processRAM = [];

        // Initialize DataTable
        const table = $('#processTable').DataTable({
            "columns": [{
                    "width": "10%"
                }, // User column
                {
                    "width": "10%"
                }, // PID column
                {
                    "width": "10%"
                }, // CPU% column
                {
                    "width": "10%"
                }, // MEM% column
                {
                    "width": "10%"
                }, // Runtime column
                {
                    "width": "40%"
                }, // Command column
                {
                    "width": "10%",
                    "orderable": false
                } // Action column
            ]
        });


        // Initialize Charts
        const cpuChart = new Chart(document.getElementById('cpuDoughnut').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Used CPU', 'Available CPU'],
                datasets: [{
                    data: [0, 100],
                    backgroundColor: ['#FF6384', '#36A2EB']
                }]
            },
            options: {
                responsive: true
            }
        });

        const ramChart = new Chart(document.getElementById('ramDoughnut').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Used RAM', 'Available RAM'],
                datasets: [{
                    data: [0, 100],
                    backgroundColor: ['#FF9F40', '#4BC0C0']
                }]
            },
            options: {
                responsive: true
            }
        });

        const historicalLine = new Chart(document.getElementById('historicalLine').getContext('2d'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'CPU%',
                    data: [],
                    borderColor: '#FF6384'
                }, {
                    label: 'RAM%',
                    data: [],
                    borderColor: '#4BC0C0'
                }]
            },
            options: {
                responsive: true
            }
        });

        // Fetch processes and update the table and charts
        function fetchCPUProcesses() {
            $.ajax({
                url: webSiteRootURL + 'admin/ffmpegMonitor.php?action=fetch',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    table.clear();
                    let totalCPU = 0;
                    let totalRAM = 0;
                    processNames.length = 0;
                    processCPU.length = 0;
                    processRAM.length = 0;

                    data.forEach(process => {
                        table.row.add([process.user, process.pid, process.cpu, process.mem, process.runtime, process.command, `<button class="btn btn-danger btn-kill btn-block" data-pid="${process.pidEncrypted}">Kill</button>`]);

                        totalCPU += parseFloat(process.cpu);
                        totalRAM += parseFloat(process.mem);

                        processNames.push(`PID: ${process.pid}`);
                        processCPU.push(parseFloat(process.cpu));
                        processRAM.push(parseFloat(process.mem));
                    });

                    table.draw();

                    // Update Doughnut Charts
                    const avgCPU = totalCPU / data.length || 0;
                    const avgRAM = totalRAM / data.length || 0;
                    cpuChart.data.datasets[0].data = [avgCPU, 100 - avgCPU];
                    ramChart.data.datasets[0].data = [avgRAM, 100 - avgRAM];
                    cpuChart.update();
                    ramChart.update();

                    // Update Historical Line Chart
                    const now = new Date().toLocaleTimeString();
                    historicalCPU.push(avgCPU);
                    historicalRAM.push(avgRAM);
                    historicalLine.data.labels.push(now);
                    historicalLine.data.datasets[0].data = historicalCPU;
                    historicalLine.data.datasets[1].data = historicalRAM;
                    historicalLine.update();
                    setTimeout(() => {
                        fetchCPUProcesses();
                    }, 2000);
                },
                error: function() {
                    alert('Failed to fetch process data.');
                }
            });
        }

        // Initial fetch
        fetchCPUProcesses();
    });
</script>