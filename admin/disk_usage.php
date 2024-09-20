<script src="<?php echo getURL('node_modules/chart.js/dist/chart.umd.js'); ?>" type="text/javascript"></script>

<div class="row">
    <div class="col-md-12">
        <canvas id="diskUsageBarChart" height="50"></canvas>
    </div>
</div>

<script>
    $(document).ready(function() {
        var data = <?php echo json_encode(getVideosDirectoryUsageInfo()); ?>;
        var isCurrentThemeDark = <?php echo json_encode(isCurrentThemeDark()); ?>; // Assuming this returns 1 or 0

        // Define text color based on theme
        var textColor = isCurrentThemeDark == 1 ? '#ffffff' : '#000000'; // White for dark theme, black for light theme

        var ctxBar = document.getElementById('diskUsageBarChart').getContext('2d');

        var usedSpace = data.used_space_bytes;
        var freeSpace = data.free_space_bytes;
        var directoryUsage = data.directory_bytes_used;

        // Calculate the used space excluding directory usage
        var actualUsedSpace = usedSpace - directoryUsage;

        // Calculate the max value for the x-axis
        var maxValue = actualUsedSpace + freeSpace + directoryUsage;

        // Stacked Bar Chart Data
        var stackedBarChartData = {
            labels: [''], // Empty label to remove 'Disk Usage' text
            datasets: [
                {
                    label: 'System',
                    data: [(actualUsedSpace / (1024 * 1024 * 1024)).toFixed(2)],
                    backgroundColor: '#36A2EB77'
                },
                {
                    label: 'Videos',
                    data: [(directoryUsage / (1024 * 1024 * 1024)).toFixed(2)],
                    backgroundColor: '#36A2EBCC'
                },
                {
                    label: 'Free',
                    data: [(freeSpace / (1024 * 1024 * 1024)).toFixed(2)],
                    backgroundColor: '#36A2EB22'
                }
            ]
        };

        // Create Stacked Bar Chart
        var diskUsageStackedBarChart = new Chart(ctxBar, {
            type: 'bar',
            data: stackedBarChartData,
            options: {
                indexAxis: 'y', // This makes the bar chart horizontal
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                        beginAtZero: true,
                        max: (maxValue / (1024 * 1024 * 1024)).toFixed(2), // Set max value for the x-axis in GB
                        ticks: {
                            color: textColor // X-axis labels color based on theme
                        }
                    },
                    y: {
                        stacked: true,
                        ticks: {
                            color: textColor // Y-axis labels color based on theme
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Disk Usage Overview (GB)',
                        color: textColor, // Title color based on theme
                        font: {
                            size: 18
                        },
                        padding: {
                            top: 10,
                            bottom: 5
                        }
                    },
                    subtitle: {
                        display: true,
                        text: data.real_path,
                        color: textColor, // Subtitle color based on theme
                        font: {
                            size: 14,
                            style: 'italic'
                        },
                        padding: {
                            bottom: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: isCurrentThemeDark == 1 ? '#333333' : '#ffffff', // Tooltip background based on theme
                        titleColor: textColor, // Tooltip title text color based on theme
                        bodyColor: textColor, // Tooltip body text color based on theme
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label + ': ' + tooltipItem.raw + ' GB';
                            }
                        }
                    },
                    legend: {
                        labels: {
                            color: textColor // Legend text color based on theme
                        }
                    }
                }
            }
        });
    });
</script>
