<div class="row">
    <div class="col-xs-12 dashboard metric-grid">
        <?php
        if (User::isAdmin()) {
        ?>
            <div class="panel panel-default panel-reveal">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fas fa-user-friends fa-2x text-success"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge total_users_online" id="total_users_online">0</div>
                            <div><?php echo __("Online Users"); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default panel-reveal">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-users fa-2x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge loading" id="totalUsers">&#8212;</div>
                            <div><?php echo __("Total Users"); ?></div>
                        </div>
                    </div>
                </div>
                <a href="<?php echo $global['webSiteRootURL']; ?>users">
                    <div class="panel-footer">
                        <span class="pull-left"><?php echo __("View Details"); ?></span>
                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        <?php
        }
        ?>
        <div class="panel panel-default panel-reveal">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-play-circle fa-2x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge loading" id="totalVideos">&#8212;</div>
                        <div><?php echo __("Total Videos"); ?></div>
                    </div>
                </div>
            </div>
            <a href="<?php echo $global['webSiteRootURL']; ?>mvideos">
                <div class="panel-footer">
                    <span class="pull-left"><?php echo __("View Details"); ?></span>
                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
        <div class="panel panel-default panel-reveal">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-eye fa-2x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge loading" id="totalVideosViews">&#8212;</div>
                        <div><?php echo __("Total Videos Views"); ?></div>
                    </div>
                </div>
            </div>
            <a href="<?php echo $global['webSiteRootURL']; ?>mvideos">
                <div class="panel-footer">
                    <span class="pull-left"><?php echo __("View Details"); ?></span>
                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
        <div class="panel panel-default panel-reveal">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="far fa-clock fa-2x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge loading" id="totalDurationVideos">&#8212;</div>
                        <div><?php echo __("Total Duration Videos (Minutes)"); ?></div>
                    </div>
                </div>
            </div>
            <a href="<?php echo $global['webSiteRootURL']; ?>mvideos">
                <div class="panel-footer">
                    <span class="pull-left"><?php echo __("View Details"); ?></span>
                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
        <div class="panel panel-default panel-reveal">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-user-plus fa-2x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge loading" id="totalSubscriptions">&#8212;</div>
                        <div><?php echo __("Total Subscriptions"); ?></div>
                    </div>
                </div>
            </div>
            <a href="<?php echo $global['webSiteRootURL']; ?>subscribes">
                <div class="panel-footer">
                    <span class="pull-left"><?php echo __("View Details"); ?></span>
                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
        <div class="panel panel-default panel-reveal">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-comments fa-2x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge loading" id="totalVideosComents">&#8212;</div>
                        <div><?php echo __("Total Video Comments"); ?></div>
                    </div>
                </div>
            </div>
            <a href="<?php echo $global['webSiteRootURL']; ?>comments">
                <div class="panel-footer">
                    <span class="pull-left"><?php echo __("View Details"); ?></span>
                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
        <div class="panel panel-default panel-reveal">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="far fa-thumbs-up fa-2x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge loading" id="totalVideosLikes">&#8212;</div>
                        <div><?php echo __("Total Videos Likes"); ?></div>
                    </div>
                </div>
            </div>
            <a href="<?php echo $global['webSiteRootURL']; ?>mvideos">
                <div class="panel-footer">
                    <span class="pull-left"><?php echo __("View Details"); ?></span>
                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
        <div class="panel panel-default panel-reveal">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="far fa-thumbs-down fa-2x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge loading" id="totalVideosDislikes">&#8212;</div>
                        <div><?php echo __("Total Videos Dislikes"); ?></div>
                    </div>
                </div>
            </div>
            <a href="<?php echo $global['webSiteRootURL']; ?>mvideos">
                <div class="panel-footer">
                    <span class="pull-left"><?php echo __("View Details"); ?></span>
                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-xs-12" id="report0ChartsContainer">
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="panel panel-default panel-reveal">
                    <div class="panel-heading">
                        <?php echo __("Today"); ?>
                    </div>
                    <div class="panel-body report-chart-body">
                        <canvas id="myChartToday"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="panel panel-default panel-reveal">
                    <div class="panel-heading">
                        <?php echo __("Last 7 Days"); ?>
                    </div>
                    <div class="panel-body report-chart-body">
                        <canvas id="myChart7"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="panel panel-default panel-reveal">
                    <div class="panel-heading">
                        <?php echo __("Last 15 Days"); ?>
                    </div>
                    <div class="panel-body report-chart-body">
                        <canvas id="myChart15"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12 col-xs-12">
                <div class="panel panel-default panel-reveal">
                    <div class="panel-heading">
                        <?php echo __("Last 30 Days"); ?>
                    </div>
                    <div class="panel-body report-chart-body">
                        <canvas id="myChart30"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12 col-xs-12">
                <div class="panel panel-default panel-reveal">
                    <div class="panel-heading">
                        <?php echo __("Last 90 Days"); ?>
                    </div>
                    <div class="panel-body report-chart-body">
                        <canvas id="myChart90"></canvas>
                    </div>
                </div>
            </div>
            <?php
            include $global['systemRootPath'] . 'view/report4.php';
            ?>
        </div>
    </div>
</div>
<script>
    function createGraph(labels, data, selector) {
        var ctx = $(selector);
        var backgroundColor = [];
        var borderColor = [];
        for (var item in data) {
            var color = randomColor();
            backgroundColor.push('rgba(' + color + ', 0.2)');
            borderColor.push('rgba(' + color + ', 1)');
        }

        var previousChart = Chart.getChart(ctx[0]);
        if (previousChart) {
            previousChart.destroy();
        }
        var textColor = getComputedStyle(ctx[0].parentElement).color;
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    label: '',
                    data: data,
                    backgroundColor: backgroundColor,
                    borderColor: borderColor,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 1000,
                    easing: 'easeOutQuart',
                    animateRotate: true,
                    animateScale: false
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return labels[context[0].dataIndex][0];
                            },
                            label: function(context) {
                                return labels[context.dataIndex][1];
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                        display: true,
                        labels: {
                            color: textColor,
                            boxWidth: 12,
                            generateLabels: function(chart) {
                                var data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    const {
                                        labels: {
                                            pointStyle
                                        }
                                    } = chart.legend.options;
                                    return data.labels.map(function(label, i) {
                                        const meta = chart.getDatasetMeta(0);
                                        const style = meta.controller.getStyle(i);

                                        return {
                                            text: chart.data.labels[i][0],
                                            fontColor: textColor,
                                            fillStyle: style.backgroundColor,
                                            strokeStyle: style.borderColor,
                                            lineWidth: style.borderWidth,
                                            pointStyle: pointStyle,
                                            hidden: !chart.getDataVisibility(i),

                                            // Extra data used for toggling the correct item
                                            index: i
                                        };
                                    });
                                } else {
                                    return [];
                                }
                            },
                            font: {
                                size: 11
                            }
                        },

                    }
                }
            }
        });
    }

    function createVideosGraphs(videos, selector) {
        var labels = [];
        var data_totalComents = [];
        var data_total_views = [];
        var data_total_likes = [];
        var data_total_dislikes = [];
        for (var index in videos) {
            var video = videos[index];
            if (typeof video == 'function') {
                continue;
            }
            labels.push(['#' + video.id + ': ' + video.total_views + ' views', video.clean_title]);
            data_totalComents.push(video.totalComents);
            data_total_views.push(video.total_views);
            data_total_likes.push(video.total_likes);
            data_total_dislikes.push(video.total_dislikes);
        }
        createGraph(labels, data_total_views, selector);
    }

    $(document).ready(function() {
        var charts = $('#report0ChartsContainer canvas').filter(function() {
            return this.id.indexOf('myChart') === 0;
        });
        var chartBodies = charts.parent();

        var numberFormatter;
        try {
            numberFormatter = new Intl.NumberFormat((document.documentElement.lang || navigator.language).replace(/_/g, '-'), {maximumFractionDigits: 0});
        } catch (error) {
            numberFormatter = new Intl.NumberFormat(undefined, {maximumFractionDigits: 0});
        }
        var countFrame;
        var displayedTotals = {};

        function animateTotals(totals, response) {
            cancelAnimationFrame(countFrame);
            var counters = [];
            $.each(totals, function(id, key) {
                var element = document.getElementById(id);
                var total = Number(response[key]);
                if (!element || response[key] === null || !Number.isFinite(total) || total < 0) {
                    return;
                }
                total = Math.round(total);
                element.classList.remove('loading');
                counters.push({element: element, id: id, from: displayedTotals[id] || 0, to: total});
            });
            var start;
            var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
            function tick(timestamp) {
                if (start === undefined) {
                    start = timestamp;
                }
                var progress = reduceMotion.matches || document.hidden ? 1 : Math.min((timestamp - start) / 1000, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                counters.forEach(function(counter) {
                    var value = progress === 1 ? counter.to : Math.round(counter.from + (counter.to - counter.from) * eased);
                    displayedTotals[counter.id] = value;
                    var text = numberFormatter.format(value);
                    if (counter.element.textContent !== text) {
                        counter.element.textContent = text;
                    }
                });
                if (progress < 1) {
                    countFrame = requestAnimationFrame(tick);
                }
            }
            countFrame = requestAnimationFrame(tick);
        }

        var loadingText = <?php echo json_encode(__('Loading...')); ?>;
        var errorText = <?php echo json_encode(__('An error occurred')); ?>;
        var emptyText = <?php echo json_encode(__('No data available in table')); ?>;
        var retryButton = $('<button type="button" class="btn btn-default btn-sm"></button>')
            .text(<?php echo json_encode(__('Retry')); ?>).hide()
            .insertBefore($('#report0ChartsContainer > .row'));

        function chartStatus(body, text, loading) {
            body.find('.report-chart-status').remove();
            body.attr('aria-busy', loading ? 'true' : 'false');
            if (text) {
                var status = $('<div class="report-chart-status" role="status"></div>');
                if (loading) {
                    status.append('<span class="loader" aria-hidden="true"></span>');
                }
                status.append($('<span></span>').text(text)).appendTo(body);
            }
        }

        function loadDashboard() {
            retryButton.hide();
            chartBodies.each(function() { chartStatus($(this), loadingText, true); });
            $.ajax({
                url: webSiteRootURL + 'view/report.json.php?isAdminPanel=<?php echo !empty($isAdminPanel) ? 1 : 0; ?>',
                dataType: 'json',
                success: function(response) {
                    if (!response || response.error) {
                        showError();
                        return;
                    }
                    var periods = ['today', 'last7Days', 'last15Days', 'last30Days', 'last90Days'];
                    if (periods.some(function(period) {
                        return !response[period] || !response[period].videos || typeof response[period].videos !== 'object';
                    })) {
                        showError();
                        return;
                    }
                    charts.each(function(index) {
                        var videos = response[periods[index]].videos;
                        chartStatus($(this).parent(), Object.keys(videos).length ? '' : emptyText, false);
                        createVideosGraphs(videos, '#' + this.id);
                    });
                    var totals = {
                        totalUsers: 'totalUsers', totalVideos: 'totalVideos',
                        totalSubscriptions: 'totalSubscriptions', totalVideosComents: 'totalComents',
                        totalVideosLikes: 'totalLikes', totalVideosDislikes: 'totalDislikes',
                        totalVideosViews: 'totalVideosViews', totalDurationVideos: 'totalDurationVideos'
                    };
                    animateTotals(totals, response);
                },
                error: showError
            });
        }

        function showError() {
            chartBodies.each(function() { chartStatus($(this), errorText, false); });
            $('.metric-grid .huge.loading').text('\u2014').removeClass('loading');
            retryButton.show();
        }

        retryButton.on('click', loadDashboard);
        loadDashboard();
    });
</script>
