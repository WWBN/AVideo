<div class="row">
    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 dashboard">
        <?php
        if (User::isAdmin()) {
            ?>
            <div class="panel panel-default <?php echo getCSSAnimationClassAndStyle('animate__flipInY', 'rep0'); ?>">
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
            <div class="panel panel-purple <?php echo getCSSAnimationClassAndStyle('animate__flipInY', 'rep0'); ?>">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-users fa-2x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge loading" id="totalUsers">0</div>
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
        <div class="panel panel-blue <?php echo getCSSAnimationClassAndStyle('animate__flipInY', 'rep0'); ?>">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-play-circle fa-2x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge loading" id="totalVideos">0</div>
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
        <div class="panel panel-primary <?php echo getCSSAnimationClassAndStyle('animate__flipInY', 'rep0'); ?>">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-eye fa-2x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge loading" id="totalVideosViews">0</div>
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
        <div class="panel panel-green <?php echo getCSSAnimationClassAndStyle('animate__flipInY', 'rep0'); ?>">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="far fa-clock fa-2x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge loading" id="totalDurationVideos">0</div>
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
        <div class="panel panel-wine <?php echo getCSSAnimationClassAndStyle('animate__flipInY', 'rep0'); ?>">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-user-plus fa-2x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge loading" id="totalSubscriptions">0</div>
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
        <div class="panel panel-red <?php echo getCSSAnimationClassAndStyle('animate__flipInY', 'rep0'); ?>">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-comments fa-2x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge loading" id="totalVideosComents">0</div>
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
        <div class="panel panel-orange <?php echo getCSSAnimationClassAndStyle('animate__flipInY', 'rep0'); ?>">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="far fa-thumbs-up fa-2x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge loading" id="totalVideosLikes">0</div>
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
        <div class="panel panel-yellow <?php echo getCSSAnimationClassAndStyle('animate__flipInY', 'rep0'); ?>">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="far fa-thumbs-down fa-2x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge loading" id="totalVideosDislikes">0</div>
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
    <div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12 <?php echo getCSSAnimationClassAndStyle('animate__fadeInUp'); ?>">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?php echo __("Today"); ?>
                    </div>
                    <div class="panel-body" >
                        <canvas id="myChartToday"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12 <?php echo getCSSAnimationClassAndStyle('animate__fadeInUp'); ?>">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?php echo __("Last 7 Days"); ?>
                    </div>
                    <div class="panel-body" >
                        <canvas id="myChart7"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-12 col-xs-12 <?php echo getCSSAnimationClassAndStyle('animate__fadeInUp'); ?>">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?php echo __("Last 15 Days"); ?>
                    </div>
                    <div class="panel-body" >
                        <canvas id="myChart15"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12 col-xs-12 <?php echo getCSSAnimationClassAndStyle('animate__fadeInUp'); ?>">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?php echo __("Last 30 Days"); ?>
                    </div>
                    <div class="panel-body" >
                        <canvas id="myChart30"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12 col-xs-12 <?php echo getCSSAnimationClassAndStyle('animate__fadeInUp'); ?>">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?php echo __("Last 90 Days"); ?>
                    </div>
                    <div class="panel-body" >
                        <canvas id="myChart90"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

    function randomColor() {
        var r = Math.floor(Math.random() * 255);
        var g = Math.floor(Math.random() * 255);
        var b = Math.floor(Math.random() * 255);
        return r + "," + g + "," + b;
    }

    function createGraph(labels, data, selector) {
        var ctx = $(selector);
        var backgroundColor = [];
        var borderColor = [];
        for (var item in data) {
            var color = randomColor();
            backgroundColor.push('rgba(' + color + ', 0.2)');
            borderColor.push('rgba(' + color + ', 1)');
        }

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
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: function (context) {
                                return context[0].label[0];
                            },
                            label: function (context) {
                                return context.label[1];
                            }
                        }
                    },
                    legend: {
                        position: 'left',
                        display: true,
                        labels: {
                            generateLabels: function (chart) {
                                var data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    const {labels: {pointStyle}} = chart.legend.options;
                                    return data.labels.map(function (label, i) {
                                        const meta = chart.getDatasetMeta(0);
                                        const style = meta.controller.getStyle(i);

                                        return {
                                            text: chart.data.labels[i][0],
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
                                size: 10
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

    $(document).ready(function () {
        $.ajax({
            url: webSiteRootURL + 'view/report.json.php?isAdminPanel=<?php echo!empty($isAdminPanel) ? 1 : 0; ?>',
            success: function (response) {
                if (!response.error) {
                    createVideosGraphs(response.today.videos, '#myChartToday');
                    createVideosGraphs(response.last7Days.videos, '#myChart7');
                    createVideosGraphs(response.last15Days.videos, '#myChart15');
                    createVideosGraphs(response.last30Days.videos, '#myChart30');
                    createVideosGraphs(response.last90Days.videos, '#myChart90');
                    if (response.totalUsers) {
                        countTo('#totalUsers', response.totalUsers);
                    }
                    countTo('#totalVideos', response.totalVideos);
                    countTo('#totalSubscriptions', response.totalSubscriptions);
                    countTo('#totalVideosComents', response.totalComents);
                    countTo('#totalVideosLikes', response.totalLikes);
                    countTo('#totalVideosDislikes', response.totalDislikes);
                    countTo('#totalVideosViews', response.totalVideosViews);
                    countTo('#totalDurationVideos', response.totalDurationVideos);
                }
            }
        });
    });
</script>