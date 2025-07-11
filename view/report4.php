<div class="col-md-12 col-sm-12 col-xs-12 <?php echo getCSSAnimationClassAndStyle('animate__fadeInUp'); ?>">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php echo __("User Registrations Over Time"); ?>
        </div>
        <div class="panel-body clearfix" style="height: 300px;">
            <canvas id="userRegistrationsChart" style="height: 300px; width: 100%;"></canvas>
        </div>
        <div class="panel-footer">
            <button class="btn btn-default btn-xs btn-block" onclick="resetZoomUserChart()">
                <i class="fa fa-search-minus"></i> <?php echo __("Reset Zoom"); ?>
            </button>
        </div>
    </div>
</div>
<div class="col-md-12 col-sm-12 col-xs-12 <?php echo getCSSAnimationClassAndStyle('animate__fadeInUp'); ?>">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php echo __("Cumulative User Growth Over Time"); ?>
        </div>
        <div class="panel-body clearfix" style="height: 300px;">
            <canvas id="userCumulativeChart" style="height: 300px; width: 100%;" tabindex="0"></canvas>
        </div>
        <div class="panel-footer">
            <button class="btn btn-default btn-xs btn-block" onclick="resetZoomUserCumulativeChart()">
                <i class="fa fa-search-minus"></i> <?php echo __("Reset Zoom"); ?>
            </button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        loadUserRegistrationsChart();
        loadUserCumulativeChart();
    });

    var userRegistrationsChartInstance;
    var userCumulativeChartInstance;

    function resetZoomUserCumulativeChart() {
        if (userCumulativeChartInstance) {
            userCumulativeChartInstance.resetZoom();
        }
    }

    function loadUserCumulativeChart() {
        $.getJSON(webSiteRootURL + 'view/report4.1.json.php', function(json) {
            const labels = Object.keys(json); // e.g., ["2023-01-01", ...]
            const data = Object.values(json).map(Number); // e.g., [1, 2, 3, 4, ...]

            const ctx = $('#userCumulativeChart');

            if (userCumulativeChartInstance) {
                userCumulativeChartInstance.destroy();
            }

            userCumulativeChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: "<?php echo __("Total Users"); ?>",
                        data: data,
                        borderColor: 'rgba(40, 167, 69, 1)',
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 0,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'day',
                                tooltipFormat: 'yyyy-MM-dd',
                                round: 'day'
                            },
                            ticks: {
                                autoSkip: true,
                                maxRotation: 0,
                                minRotation: 0
                            },
                            title: {
                                display: true,
                                text: "<?php echo __("Date"); ?>"
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grace: '5%',
                            title: {
                                display: true,
                                text: "<?php echo __("Total Users"); ?>"
                            }
                        }
                    },
                    plugins: {
                        zoom: {
                            pan: {
                                enabled: true,
                                mode: 'x',
                                modifierKey: null
                            },
                            zoom: {
                                drag: {
                                    enabled: true,
                                    backgroundColor: 'rgba(40, 167, 69, 0.3)'
                                },
                                pinch: {
                                    enabled: true
                                },
                                mode: 'x'
                            }
                        },
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        });
    }

    function resetZoomUserChart() {
        if (userRegistrationsChartInstance) {
            userRegistrationsChartInstance.resetZoom();
        }
    }

    function loadUserRegistrationsChart() {
        $.getJSON(webSiteRootURL + 'view/report4.json.php', function(json) {
            const labels = Object.keys(json); // ["2024-07-01", "2024-07-02", ...]
            const data = Object.values(json).map(Number); // [5, 3, 8, 2, ...]

            const ctx = $('#userRegistrationsChart');

            if (userRegistrationsChartInstance) {
                userRegistrationsChartInstance.destroy();
            }

            userRegistrationsChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: "<?php echo __("New Users per Day"); ?>",
                        data: data,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'day', // You can change to 'week' or 'month' if too dense
                                tooltipFormat: 'yyyy-MM-dd',
                                round: 'day'
                            },
                            ticks: {
                                autoSkip: true,
                                maxRotation: 0,
                                minRotation: 0
                            },
                            title: {
                                display: true,
                                text: "<?php echo __("Date"); ?>"
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grace: '5%',
                            title: {
                                display: true,
                                text: "<?php echo __("User Count"); ?>"
                            }
                        }
                    },
                    plugins: {
                        zoom: {
                            pan: {
                                enabled: true,
                                mode: 'x',
                                modifierKey: null
                            },
                            zoom: {
                                drag: {
                                    enabled: true,
                                    backgroundColor: 'rgba(0, 123, 255, 0.3)'
                                },
                                pinch: {
                                    enabled: true
                                },
                                mode: 'x'
                            }
                        },
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        });
    }
</script>
