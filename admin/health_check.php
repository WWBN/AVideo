<?php
// Include centralized health check functions and configuration
require_once __DIR__ . '/health_check_functions.php';

//socket
// live
// max upload size max file size
// crontabs

$phpExtensions = [];
$phpExtensions[] = ['pdo_mysql'];
$phpExtensions[] = ['curl'];
$phpExtensions[] = ['gd', 'Important to generate images'];
$phpExtensions[] = ['xml', 'Important to get the live stats'];
$phpExtensions[] = ['zip', 'Important handle HLS files'];
$phpExtensions[] = ['mbstring', 'Handle multi-byte character encodings'];
// $phpExtensions[] = array('mbstring'); // I could not detect that

$apacheModules = [];
$apacheModules[] = ['mod_php', 'We strongly recommend you to not use PHP-fpm, use PHP module instead'];
$apacheModules[] = ['mod_xsendfile', 'https://github.com/WWBN/AVideo/wiki/Install-Apache-XSendFIle'];
$apacheModules[] = ['mod_rewrite', 'sudo a2enmod rewrite'];
$apacheModules[] = ['mod_expires', 'sudo a2enmod expires'];
$apacheModules[] = ['mod_headers', 'sudo a2enmod headers'];

$linuxApps = [];
if (!isDocker()) {
    $linuxApps[] = ['mysql'];
}
$linuxApps[] = ['ffmpeg'];
$linuxApps[] = ['git'];
$linuxApps[] = ['exiftool'];
$linuxApps[] = ['unzip'];
$linuxApps[] = ['youtube-dl'];
$linuxApps[] = ['sshpass', 'https://github.com/WWBN/AVideo/wiki/Clone-Site-Plugin#the-process-with-rsync-support-hls'];
$linuxApps[] = ['apache2'];
$linuxApps[] = ['convert', 'sudo apt update && sudo apt install imagemagick'];

$messages = ['Server' => [], 'PHP' => [], 'Apache' => []];
$version = phpversion();
$phpMinVersion = '8.0.0';
if (strnatcmp($version, $phpMinVersion) >= 0) {
    $messages['PHP'][] = "PHP v{$version}";
} else {
    $messages['PHP'][] = "PHP v{$version}, please upgrade to version {$phpMinVersion} or greater";
}

$extensions = array_map('strtolower', get_loaded_extensions());
//var_dump($extensions);
foreach ($phpExtensions as $value) {
    if (in_array($value[0], $extensions)) {
        $messages['PHP'][] = $value[0];
    } else {
        $messages['PHP'][] = [$value[0], 'sudo apt-get install php-' . str_replace('_', '-', $value[0]) . ' -y && sudo /etc/init.d/apache2 restart'];
    }
}

if (function_exists('imagewebp')) {
    $messages['PHP'][] = "WebP is supported";
} else {
    $messages['PHP'][] = ["WebP is not supported", 'sudo apt-get install -y libwebp-dev libjpeg-dev libpng-dev libxpm-dev libfreetype6-dev php-gd'];
}

if (isset($_SERVER["HTTPS"])) {
    $messages['Apache'][] = "HTTPS is enabled";
} else {
    $messages['Apache'][] = ["HTTPS is not enabled", 'https://github.com/WWBN/AVideo/wiki/Why-use-HTTPS'];
}

$XSendFileURL = "{$global['webSiteRootURL']}videos/test.mp4";
$XSendFilePath = "{$global['systemRootPath']}view/xsendfile.html";
$XSendFile = url_get_contents($XSendFileURL);
$XSendFileOriginal = file_get_contents($XSendFilePath);
//var_dump($XSendFileURL, $XSendFilePath, $XSendFile, $XSendFileOriginal);exit;
if ($XSendFile === $XSendFileOriginal) {
    $messages['Apache'][] = "XSendFIle is enabled";
} else {
    $messages['Apache'][] = ["XSendFIle is not enabled", 'https://github.com/WWBN/AVideo/wiki/Install-Apache-XSendFIle'];
}

if (function_exists('apache_get_modules')) {
    $mods = array_map('strtolower', apache_get_modules());
    //var_dump($mods);
    foreach ($apacheModules as $value) {
        if (in_array($value[0], $mods)) {
            $messages['Apache'][] = $value[0];
        } else {
            $found = false;
            foreach ($mods as $value2) {
                if (preg_match("/{$value[0]}/", $value2)) {
                    $found = $value2;
                    break;
                }
            }
            if ($found) {
                $messages['Apache'][] = $found;
            } else {
                $messages['Apache'][] = [$value[0], @$value[1]];
            }
        }
    }
} else {
    foreach ($apacheModules as $value) {
        $messages['Apache'][] = [$value[0], 'We could not check your installed modules. We recommend you to use apache as a module NOT as a FPM'];
    }
}
/*
if(_isSocketPresentOnCrontab()){
    $messages['Server'][] = "Socket is installed on your crontab";
}else{
    $messages['Server'][] = ["Socket is NOT installed on your crontab, open your terminal and type 'crontab -e', than add the code: ", "@reboot sleep 60;nohup php {$global['systemRootPath']}plugin/YPTSocket/server.php &"];
}

if(_isSchedulerPresentOnCrontab()){
    $messages['Server'][] = "Scheduler plugin is installed on your crontab";
}else{
    $messages['Server'][] = ["Scheduler plugin is NOT installed on your crontab, open your terminal and type 'crontab -e', than add the code: ", "* * * * * php {$global['systemRootPath']}plugin/Scheduler/run.php"];
}
 *
 */

foreach ($linuxApps as $value) {
    $response = _isAPPInstalled($value[0]);
    if (!empty($response)) {
        $messages['Server'][] = "{$value[0]} is installed here {$response}";
    } else {
        $messages['Server'][] = ["{$value[0]} is NOT installed", @$value[1]];
    }
}
$videosDir = getVideosDir();
if (is_writable($videosDir)) {
    $messages['Server'][] = "{$videosDir} is writable";
} else {
    $messages['Server'][] = ["{$videosDir} is NOT writable", 'sudo chmod -R 777 ' . $videosDir];
}

if (isDocker()) {
    $messages['Server'][] = "Log is in the docker output";
} else if (is_writable($global['logfile'])) {
    $messages['Server'][] = "Log file is writable";
} else {
    $messages['Server'][] = ["{$global['logfile']} is NOT writable", 'sudo chmod -R 777 ' . $global['logfile']];
}

$cacheDir = "{$videosDir}cache/";
if (is_writable($cacheDir)) {
    $messages['Server'][] = "Cache is writable";
} else {
    $messages['Server'][] = ["{$cacheDir} is NOT writable", 'sudo chmod -R 777 ' . $cacheDir];
}

$_50GB = 53687091200;

$df = disk_free_space("/");
if ($df > $_50GB) {
    $messages['Server'][] = "You have enough free disk space " . humanFileSize($df);
} else {
    $messages['Server'][] = ["Your disk is almost full, you have only " . humanFileSize($df) . ' free'];
}

$dfVideos = disk_free_space($videosDir);
if ($dfVideos > $_50GB) {
    $messages['Server'][] = "You have enough free disk space for the videos directory " . humanFileSize($dfVideos);
} else {
    $messages['Server'][] = ["Your videos directory is almost full, you have only " . humanFileSize($dfVideos) . ' free'];
}

// Disk type detection
$diskType = getDiskType("/");
$messages['Server'][] = "Root disk type: {$diskType}";

$videoDiskType = getDiskType($videosDir);
if ($videoDiskType !== $diskType) {
    $messages['Server'][] = "Videos directory disk type: {$videoDiskType}";
}

// Disk I/O speed test
$ioSpeed = getDiskIOSpeed($videosDir);
if ($ioSpeed['read'] !== 'N/A' || $ioSpeed['write'] !== 'N/A') {
    $messages['Server'][] = "Disk speed - Read: {$ioSpeed['read']}, Write: {$ioSpeed['write']}";
}

// Evaluate disk performance and show warnings
$diskEval = evaluateDiskPerformance(
    $videoDiskType,
    isset($ioSpeed['readSpeed']) ? $ioSpeed['readSpeed'] : null,
    isset($ioSpeed['writeSpeed']) ? $ioSpeed['writeSpeed'] : null
);

foreach ($diskEval['warnings'] as $warning) {
    $messages['Server'][] = [$warning, 'Critical performance issue'];
}

foreach ($diskEval['recommendations'] as $recommendation) {
    $messages['Server'][] = [$recommendation];
}

// Internet speed test will be loaded async via AJAX

$verified = verify($global['webSiteRootURL']);

if (empty($verified)) {
    $messages['Server'][] = ["We could not verify your server from outside {$global['webSiteRootURL']}"];
} else {
    if (!empty($verified->verified)) {
        $messages['Server'][] = "Server Checked from outside: <br>" . implode('<br>', $verified->msg);
    } else {
        $messages['Server'][] = ["Something is wrong: ", implode('<br>', $verified->msg)];
    }
    /*
      if(!empty($verified->screenshot)){
      $messages['Server'][] = "<img src='$verified->screenshot' class='img img-responsive'>";
      }
     *
     */
}

if (Scheduler::isActive()) {
    $messages['Server'][] = "Scheduler plugin crontab is runing";
} else {
    $reason = Scheduler::whyIsActive();
    $messages['Server'][] = ["Scheduler plugin crontab is NOT runing", Scheduler::getCronHelp() . '<br>' . $reason];
}

$messages['portsExternal'] = array();
$messages['portsInternal'] = array();

$varables = getPorts();
foreach ($varables as $port => $name) {
    $isOpen = isPortOpenExternal($port, 4);
    if ($isOpen) {
        $messages['portsExternal'][] = "{$name} (Port {$port}) is externally accessible.";
    } else {
        $messages['portsExternal'][] = ["{$name} (Port {$port}) is not externally accessible."];
    }
    $isOpen = isLocalPortOpen($port);
    if ($isOpen) {
        $messages['portsInternal'][] = "{$name} (Port {$port}) is accessible within the local network.";
    } else {
        $messages['portsInternal'][] = ["{$name} (Port {$port}) is not accessible within the local network."];
    }
}

function printMessages($messages, $cols = array(4, 6))
{
?>
    <div class="row">
        <?php
        $count = 0;
        foreach ($messages as $value) {
            $count++;
            if (is_array($value)) {
        ?>
                <div class="col-lg-<?php echo $cols[0]; ?> col-md-<?php echo $cols[1]; ?> <?php echo getCSSAnimationClassAndStyle('animate__flipInX'); ?>">
                    <div class="alert alert-danger">
                        <i class="fas fa-times"></i>
                        <?php
                        echo $value[0];
                        if (!empty($value[1])) {
                            if (preg_match('/^http/i', $value[1])) {
                        ?>
                                <a href="<?php echo $value[1]; ?>" class="btn btn-danger btn-xs btn-block" target="_blank"><i class="fas fa-hand-holding-medical"></i> </a>
                            <?php
                            } else {
                            ?>
                                <br><code><?php echo $value[1]; ?></code>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php
            } else {
            ?>
                <div class="col-lg-<?php echo $cols[0]; ?> col-md-<?php echo $cols[1]; ?> <?php echo getCSSAnimationClassAndStyle('animate__flipInX'); ?>">
                    <div class="alert alert-success">
                        <i class="fas fa-check"></i> <?php echo $value; ?>
                    </div>
                </div>
        <?php
            }
            if ($count % (12 / $cols[1]) === 0) {
                echo '<div class="clearfix visible-md"></div>';
            }
            if ($count % (12 / $cols[0]) === 0) {
                echo '<div class="clearfix visible-lg"></div>';
            }
        }

        ?>
    </div>
<?php
}


?>
<style>
    #healthCheck .alert {
        overflow: auto;
    }
    #performanceMetrics .metric-box {
        background: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        text-align: center;
    }
    #performanceMetrics .metric-box.dark {
        background: #2b2b2b;
        border-color: #444;
    }
    #performanceMetrics .metric-label {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    #performanceMetrics .metric-value {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }
    #performanceMetrics .metric-value.dark {
        color: #fff;
    }
    #performanceMetrics .metric-status {
        font-size: 11px;
        margin-top: 5px;
        color: #999;
    }
    #performanceMetrics .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 10px auto;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    #performanceMetrics .metric-icon {
        font-size: 36px;
        margin-bottom: 10px;
    }
</style>
<div class="panel panel-default" id="healthCheck">
    <div class="panel-heading">
        <?php
        echo '<h1>' . PHP_OS;
        if (isDocker()) {
            echo ' <small>(Docker)</small>';
        }
        echo '</h1>';
        ?>
    </div>
    <div class="panel-body">
        <?php include __DIR__ . '/disk_usage.php'; ?>

        <div class="panel panel-default" id="performanceMetrics" style="margin-top: 20px;">
            <div class="panel-heading">
                <h4><i class="fas fa-tachometer-alt"></i> Performance Metrics</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="metric-box" id="downloadMetric">
                            <div class="metric-icon"><i class="fas fa-download"></i></div>
                            <div class="metric-label">Download Speed</div>
                            <div class="metric-value"><div class="spinner"></div></div>
                            <div class="metric-status">Testing...</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="metric-box" id="uploadMetric">
                            <div class="metric-icon"><i class="fas fa-upload"></i></div>
                            <div class="metric-label">Upload Speed</div>
                            <div class="metric-value"><div class="spinner"></div></div>
                            <div class="metric-status">Testing...</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="metric-box" id="pingMetric">
                            <div class="metric-icon"><i class="fas fa-signal"></i></div>
                            <div class="metric-label">Ping / Latency</div>
                            <div class="metric-value"><div class="spinner"></div></div>
                            <div class="metric-status">Testing...</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="metric-box" id="diskMetric">
                            <div class="metric-icon"><i class="fas fa-hdd"></i></div>
                            <div class="metric-label">Disk Type</div>
                            <div class="metric-value"><?php echo getDiskType('/'); ?></div>
                            <div class="metric-status"><?php
                                $videoDiskType = getDiskType(getVideosDir());
                                $rootDiskType = getDiskType('/');
                                if ($videoDiskType !== $rootDiskType) {
                                    echo "Videos: {$videoDiskType}";
                                }
                            ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <div class="row">
            <div class="col-lg-8 col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Ports
                    </div>
                    <div class="panel-body">
                        <?php
                        printMessages($messages['portsInternal']);
                        ?>
                    </div>
                    <div class="panel-footer">
                        <?php
                        printMessages($messages['portsExternal']);
                        ?>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Server
                    </div>
                    <div class="panel-body">
                        <?php
                        printMessages($messages['Server']);
                        ?>
                    </div>
                    <div class="panel-footer">
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        PHP
                    </div>
                    <div class="panel-body">
                        <?php
                        printMessages($messages['PHP'], array(12, 12));
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Apache
                    </div>
                    <div class="panel-body">
                        <?php
                        printMessages($messages['Apache'], array(12, 12));
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var isDark = <?php echo json_encode(isCurrentThemeDark()); ?>;

    // Apply dark theme if needed
    if (isDark) {
        $('#performanceMetrics .metric-box').addClass('dark');
        $('#performanceMetrics .metric-value').addClass('dark');
    }

    // Function to update metric display
    function updateMetric(metricId, value, status, icon) {
        var $metric = $('#' + metricId);
        $metric.find('.metric-value').html(value);
        $metric.find('.metric-status').html(status || '');
        if (icon) {
            $metric.find('.metric-icon i').removeClass().addClass(icon);
        }
    }

    // Load internet speed test asynchronously
    $.ajax({
        url: '<?php echo $global['webSiteRootURL']; ?>view/ajax/getInternetSpeed.json.php',
        method: 'POST',
        dataType: 'json',
        timeout: 30000, // 30 second timeout
        success: function(response) {
            if (response.error) {
                updateMetric('downloadMetric', 'Error', response.msg || 'Test failed', 'fas fa-exclamation-triangle');
                updateMetric('uploadMetric', 'Error', 'Test failed', 'fas fa-exclamation-triangle');
                updateMetric('pingMetric', 'Error', 'Test failed', 'fas fa-exclamation-triangle');
            } else {
                // Update download speed
                var downloadColor = 'text-success';
                var downloadSpeed = parseFloat(response.download);
                if (downloadSpeed < 10) downloadColor = 'text-danger';
                else if (downloadSpeed < 50) downloadColor = 'text-warning';

                updateMetric('downloadMetric',
                    '<span class="' + downloadColor + '">' + response.download + '</span>',
                    getSpeedRating(downloadSpeed, 'download'),
                    'fas fa-download'
                );

                // Update upload speed
                if (response.upload !== 'N/A' && response.upload !== 'Test failed') {
                    var uploadColor = 'text-success';
                    var uploadSpeed = parseFloat(response.upload);
                    if (uploadSpeed < 5) uploadColor = 'text-danger';
                    else if (uploadSpeed < 20) uploadColor = 'text-warning';

                    updateMetric('uploadMetric',
                        '<span class="' + uploadColor + '">' + response.upload + '</span>',
                        getSpeedRating(uploadSpeed, 'upload'),
                        'fas fa-upload'
                    );
                } else {
                    updateMetric('uploadMetric', 'N/A', 'Test unavailable', 'fas fa-upload');
                }

                // Update ping
                var pingValue = parseInt(response.ping);
                var pingColor = 'text-success';
                if (pingValue > 200) pingColor = 'text-danger';
                else if (pingValue > 100) pingColor = 'text-warning';

                updateMetric('pingMetric',
                    '<span class="' + pingColor + '">' + response.ping + '</span>',
                    getPingRating(pingValue),
                    'fas fa-signal'
                );

                // Display warnings and recommendations if any
                if (response.warnings && response.warnings.length > 0) {
                    var warningHtml = '<div class="alert alert-warning" style="margin-top: 15px;"><strong><i class="fas fa-exclamation-triangle"></i> Performance Warnings:</strong><ul style="margin: 10px 0 0 0;">';
                    response.warnings.forEach(function(warning) {
                        warningHtml += '<li>' + warning + '</li>';
                    });
                    warningHtml += '</ul></div>';
                    $('#performanceMetrics .panel-body').append(warningHtml);
                }

                if (response.recommendations && response.recommendations.length > 0) {
                    var recHtml = '<div class="alert alert-info" style="margin-top: 15px;"><strong><i class="fas fa-lightbulb"></i> Recommendations:</strong><ul style="margin: 10px 0 0 0;">';
                    response.recommendations.forEach(function(rec) {
                        recHtml += '<li>' + rec + '</li>';
                    });
                    recHtml += '</ul></div>';
                    $('#performanceMetrics .panel-body').append(recHtml);
                }

                // Add performance level badge
                if (response.performanceLevel) {
                    var badgeClass = 'info';
                    var badgeText = response.performanceLevel.toUpperCase();
                    if (response.performanceLevel === 'excellent') badgeClass = 'success';
                    else if (response.performanceLevel === 'good') badgeClass = 'primary';
                    else if (response.performanceLevel === 'fair') badgeClass = 'warning';
                    else if (response.performanceLevel === 'poor') badgeClass = 'danger';

                    var badgeHtml = '<div style="text-align: center; margin-top: 15px;"><h4>Overall Performance: <span class="label label-' + badgeClass + '">' + badgeText + '</span></h4></div>';
                    $('#performanceMetrics .panel-body').append(badgeHtml);
                }
            }
        },
        error: function(xhr, status, error) {
            var errorMsg = 'Connection timeout';
            if (status === 'timeout') {
                errorMsg = 'Test timed out';
            }
            updateMetric('downloadMetric', 'Error', errorMsg, 'fas fa-exclamation-triangle');
            updateMetric('uploadMetric', 'Error', errorMsg, 'fas fa-exclamation-triangle');
            updateMetric('pingMetric', 'Error', errorMsg, 'fas fa-exclamation-triangle');
        }
    });

    function getSpeedRating(speed, type) {
        if (type === 'download') {
            if (speed >= 100) return 'Excellent';
            if (speed >= 50) return 'Good';
            if (speed >= 10) return 'Fair';
            return 'Poor';
        } else { // upload
            if (speed >= 50) return 'Excellent';
            if (speed >= 20) return 'Good';
            if (speed >= 5) return 'Fair';
            return 'Poor';
        }
    }

    function getPingRating(ping) {
        if (ping <= 50) return 'Excellent';
        if (ping <= 100) return 'Good';
        if (ping <= 200) return 'Fair';
        return 'Poor';
    }
});
</script>
