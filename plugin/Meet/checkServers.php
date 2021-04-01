<?php
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}

$obj = AVideoPlugin::getObjectDataIfEnabled("Meet");
//_error_log(json_encode($_SERVER));
if (empty($obj)) {
    die("Plugin disabled");
}

if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}

if (!empty($_GET['newServer'])) {
    $p = AVideoPlugin::loadPluginIfEnabled("Meet");
    $p->setDataObjectParameter("server->value", preg_replace("/[^0-1a-z.]/i", "", $_GET['newServer']));
}

$m = AVideoPlugin::loadPlugin("Meet");
$emptyObject = $m->getEmptyDataObject();

$timeouts = 2000;
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Check Meet Servers") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <a href="<?php echo $global['webSiteRootURL']; ?>plugin/Meet/"  class="btn btn-default" data-toggle="tooltip" title="<?php echo __("Create a Meet"); ?> " data-placement="bottom" >
                        <i class="fas fa-comments"></i> <?php echo __("Create a Meet"); ?>
                    </a>
                </div>
                <div class="panel-body tabbable-line">
                    <div class="row">
                        <?php
                        foreach ($emptyObject->server->type as $key => $value) {
                            if ($key == "custom") {
                                ?>
                                <div class="col-xs-6">
                                    <div class="panel panel-default" id="panel<?php echo $newKey; ?>">
                                        <div class="panel-heading ">
                                            <?php
                                            echo "<b>{$value} ({$obj->CUSTOM_JITSI_DOMAIN})</b> ";
                                            if ($obj->server->value !== $key) {
                                                ?>
                                                <a href="<?php echo $global['webSiteRootURL']; ?>plugin/Meet/?newServer=<?php echo $key; ?>" data-toggle="tooltip" data-placement="bottom" title="Change to (<?php echo $value; ?>) server" >
                                                    <i class="fas fa-random" ></i>
                                                </a>
                                                <?php
                                            } else {
                                                ?>
                                                <a href="<?php echo $global['webSiteRootURL']; ?>plugin/Meet/" data-toggle="tooltip" data-placement="bottom" title="Stay on (<?php echo $value; ?>)" >
                                                    <i class="fas fa-check" ></i>
                                                </a>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            } else {
                                $newKey = str_replace(".", "_", $key);
                                ?>
                                <div class="col-xs-6">
                                    <div class="panel panel-default" id="panel<?php echo $newKey; ?>">
                                        <div class="panel-heading ">
                                            <?php
                                            echo "<b>{$value}</b> ";
                                            if ($obj->server->value !== $key) {
                                                ?>
                                                <a href="<?php echo $global['webSiteRootURL']; ?>plugin/Meet/?newServer=<?php echo $key; ?>" data-toggle="tooltip" data-placement="bottom" title="Change to (<?php echo $value; ?>) server" >
                                                    <i class="fas fa-random" ></i>
                                                </a>
                                                <?php
                                            } else {
                                                ?>
                                                <a href="<?php echo $global['webSiteRootURL']; ?>plugin/Meet/" data-toggle="tooltip" data-placement="bottom" title="Stay on (<?php echo $value; ?>)" >
                                                    <i class="fas fa-check" ></i>
                                                </a>
                                                <?php
                                            }
                                            ?>
                                            <span class="label label-primary grade pull-right" id="grade<?php echo $newKey; ?>">
                                                <i class="fas fa-cog"></i>
                                            </span>

                                        </div>
                                        <div class="panel-body">
                                            <ul class="list-group">
                                                <li class="list-group-item">
                                                    <i class="fas fa-download"></i> Download Speed
                                                    <small class="text-muted" id='gradespeed<?php echo $newKey; ?>'></small>
                                                    <span class="badge" id='speed<?php echo $newKey; ?>'>
                                                        <i class="fas fa-cog"></i>
                                                    </span>
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="fas fa-upload"></i> Upload Speed
                                                    <small class="text-muted" id='gradeUspeed<?php echo $newKey; ?>'></small>
                                                    <span class="badge" id='Uspeed<?php echo $newKey; ?>'>
                                                        <i class="fas fa-cog"></i>
                                                    </span>
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="fas fa-stopwatch"></i> Response Time
                                                    <small class="text-muted" id='graderesponse<?php echo $newKey; ?>'></small>
                                                    <span class="badge" id='response<?php echo $newKey; ?>'>
                                                        <i class="fas fa-cog"></i>
                                                    </span>
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="fas fa-network-wired"></i> Sites Active
                                                    <small class="text-muted" id='gradetotalSitesActive<?php echo $newKey; ?>'></small>
                                                    <span class="badge" id='totalSitesActive<?php echo $newKey; ?>'>
                                                        <i class="fas fa-cog"></i>
                                                    </span>
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="fas fa-video"></i> Streamers Services
                                                    <small class="text-muted" id='gradeStreamersServices<?php echo $newKey; ?>'></small>
                                                    <span class="badge" id='StreamersServices<?php echo $newKey; ?>'>
                                                        <i class="fas fa-cog"></i>
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="alert alert-info">
                                Before Change the server make sure you do not have any meeting running.
                                Other users may not able to connect after you change it.
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="alert alert-info">
                                Higher grade, means the server is better for a meeting.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            include $global['systemRootPath'] . 'view/include/footer.php';
            ?>
            <script>

                var roundedDecimals = 2;
                var bytesInAKilobyte = 1024;
                var tries = 1;
                var timeouts = <?php echo $timeouts; ?>;

                function speed(bitsPerSecond) {
                    var Kbps = (bitsPerSecond / bytesInAKilobyte).toFixed(roundedDecimals);
                    if (Kbps <= 1)
                        return {value: bitsPerSecond, units: "Bps"};
                    var MBps = (Kbps / bytesInAKilobyte).toFixed(roundedDecimals);
                    //if (MBps <= 1)
                    return {value: Kbps, units: "Kbps", text: Kbps + " Kbps", bitsPerSecond: bitsPerSecond};
                    //else
                    //return {value: MBps, units: "MBps", text: MBps + " MBps", bitsPerSecond: bitsPerSecond};
                }

                function checkSpeed(server) {
                    var imageAddr = "https://" + server + "/jesus.png?n=" + Math.random();
                    var startTime, endTime;
                    var downloadSize = 3881702;//3.70 MB
                    var download = new Image();
                    var serverId = server.replace(/[.]/g, "_");
                    var speedId = '#speed' + serverId;
                    var gradespeedId = '#gradespeed' + serverId;
                    download.onload = function () {
                        endTime = (new Date()).getTime();
                        var duration = (endTime - startTime) / 1000;
                        var bitsLoaded = downloadSize * 8;
                        var speedBps = (bitsLoaded / duration).toFixed(roundedDecimals);
                        var response = speed(speedBps);
                        console.log(speedBps);
                        $(gradespeedId).html("+" + (speedBps / 1000000).toFixed(2));
                        sitesGrade[server] += (speedBps / 1000000);

                        animateValue('speed' + serverId, 0, response.value, timeouts * 2, "", response.units);
                    }
                    download.onerror = function () {
                        $(speedId).html(response.text);
                    }
                    startTime = (new Date()).getTime();
                    download.src = imageAddr;
                }

                function checkServerUsage(server) {
                    var ajaxTime = new Date().getTime();
                    $.ajax({
                        url: "https://" + server + "/api/info.json.php",
                    }).done(function (response) {
                        var totalTime = new Date().getTime() - ajaxTime;
                        var serverId = server.replace(/[.]/g, "_");

                        animateValue('response' + serverId, 0, totalTime, timeouts * 2, "", "ms");
                        animateValue('totalSitesActive' + serverId, 0, response.totalSitesActive, timeouts * 2, "", "/" + (response.totalSitesActive + response.totalSitesInactive));
                        animateValue('StreamersServices' + serverId, 0, response.totalOnlineLiveStreamersServices, timeouts * 2, "", "/" + (response.totalLiveStreamersServices));

                        // calculate grade
                        sitesGrade[server] -= (response.totalOnlineLiveStreamersServices * 10);
                        sitesGrade[server] -= (response.totalLiveStreamersServices);
                        sitesGrade[server] -= (response.totalSitesActive);
                        sitesGrade[server] -= (totalTime / 50);

                        $('#graderesponse' + serverId).html("-" + (totalTime / 50).toFixed(2));
                        $('#gradetotalSitesActive' + serverId).html("-" + (response.totalSitesActive).toFixed(2));
                        $('#gradeStreamersServices' + serverId).html("-" + ((response.totalOnlineLiveStreamersServices * 10) + (response.totalLiveStreamersServices)).toFixed(2));

                        // Here I want to get the how long it took to load some.php and use it further
                    }).fail(function (jqXHR, textStatus) {
                        tries++;
                        setTimeout(function () {
                            checkServerUsage(server);
                        }, tries * timeouts);
                    });
                }

                function check(server) {
                    checkSpeed(server);
                    checkServerUsage(server);
                }

                function checkGrades() {
                    bestGrade = -1;
                    bestGradeServer = '';
                    for (const server in sitesGrade) {
                        if (bestGrade < sitesGrade[server]) {
                            bestGrade = sitesGrade[server];
                            bestGradeServer = server;
                        }
                        var serverId = server.replace(/[.]/g, "_");
                        $('#grade' + serverId).html("Grade: " + (sitesGrade[server].toFixed(2)));
                    }
                    var serverId = bestGradeServer.replace(/[.]/g, "_");
                    $('.grade').not('#grade' + serverId).removeClass('label-success');
                    $('.grade').not('#grade' + serverId).addClass('label-primary');
                    $('#grade' + serverId).addClass('label-success');
                    $('#grade' + serverId).removeClass('label-primary');
                }

                function checkUploadSpeed(server, iterations, update) {
                    var average = 0,
                            index = 0,
                            timer = window.setInterval(check, 5000);
                    check();

                    function check() {
                        var xhr = new XMLHttpRequest(),
                                url = 'https://' + server + '?cache=' + Math.floor(Math.random() * 10000), //prevent url cache
                                data = getRandomString(1), //1 meg POST size handled by all servers
                                startTime,
                                speed = 0;
                        xhr.onreadystatechange = function (event) {
                            if (xhr.readyState == 4) {
                                speed = Math.round(1024 / ((new Date() - startTime) / 1000));
                                average == 0
                                        ? average = speed
                                        : average = Math.round((average + speed) / 2);
                                update(speed, average);
                                index++;
                                if (index == iterations) {
                                    window.clearInterval(timer);
                                }
                                ;
                            }
                            ;
                        };
                        xhr.open('POST', url, true);
                        startTime = new Date();
                        xhr.send(data);
                    }
                    ;

                    function getRandomString(sizeInMb) {
                        var chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789~!@#$%^&*()_+`-=[]\{}|;':,./<>?", //random data prevents gzip effect
                                iterations = sizeInMb * 1024 * 1024, //get byte count
                                result = '';
                        for (var index = 0; index < iterations; index++) {
                            result += chars.charAt(Math.floor(Math.random() * chars.length));
                        }
                        ;
                        return result;
                    }
                    ;
                }

                function runAll(server) {
                    var serverId = server.replace(/[.]/g, '_');
                    $('#panel' + serverId + " .fa-cog").addClass('fa-spin');
                    sitesGrade[server] = 100;
                    check(server);
                    checkUploadSpeed(server, 1, function (speed, average) {
                        var serverId = server.replace(/[.]/g, '_');

                        animateValue('Uspeed' + serverId, 0, average, timeouts * 2, "", "Kbps");

                        if (previewsGrade[server]) {
                            sitesGrade[server] -= previewsGrade[server];
                        }
                        previewsGrade[server] = (average / 50);
                        sitesGrade[server] += previewsGrade[server];

                        $('#gradeUspeed' + serverId).html('+' + previewsGrade[server].toFixed(2));
                    });
                }

                function animateValue(id, start, end, duration, prepend, postpend) {
                    // assumes integer values for start and end

                    var obj = document.getElementById(id);
                    var range = end - start;
                    // no timer shorter than 50ms (not really visible any way)
                    var minTimer = 50;
                    // calc step time to show all interediate values
                    var stepTime = Math.abs(Math.floor(duration / range));

                    // never go below minTimer
                    stepTime = Math.max(stepTime, minTimer);

                    // get current time and calculate desired end time
                    var startTime = new Date().getTime();
                    var endTime = startTime + duration;
                    var timer;

                    function run() {
                        var now = new Date().getTime();
                        var remaining = Math.max((endTime - now) / duration, 0);
                        var value = Math.round(end - (remaining * range));
                        obj.innerHTML = prepend + " " + value + " " + postpend;
                        if (value == end) {
                            clearInterval(timer);
                        }
                    }

                    timer = setInterval(run, stepTime);
                    run();
                }

                var previewsGrade = {};

                var sitesGrade = {};
                $(document).ready(function () {

<?php
$count = 0;
foreach ($emptyObject->server->type as $key => $value) {
    if ($key == 'custom') {
        continue;
    }
    $count++;
    echo "setTimeout(function(){runAll('{$key}')}," . ($count * $timeouts) . ");";
}
?>
                    setInterval(function () {
                        checkGrades();
                    }, 1000);
                });
            </script>
        </div>
    </body>
</html>
