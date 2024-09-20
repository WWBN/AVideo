<?php

function _isAPPInstalled($appName)
{
    $appName = preg_replace('/[^a-z0-9_-]/i', '', $appName);
    return trim(shell_exec("which {$appName}"));
}

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

$messages = ['Server' => [], 'PHP' => [], 'Apache' => []];
$version = phpversion();
$phpMinVersion = '7.3.0';
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

$ports = json_decode(checkPorts());
$ports = object_to_array($ports);
$varables = getPorts();
foreach ($ports['ports'] as $key => $value) {
    $name = $varables[$value['port']];
    if ($value['isOpen']) {
        $messages['Server'][] = "{$name} Port {$value['port']} is open";
    } else {
        $messages['Server'][] = ["{$name} Port {$value['port']} is closed"];
    }
}
?>
<style>
    #healthCheck .alert {
        overflow: auto;
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
    </div>
    <div class="panel-footer">
        <div class="row">
            <div class="col-lg-8 col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Server
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <?php
                            $count = 0;
                            foreach ($messages['Server'] as $value) {
                                $count++;
                                if (is_array($value)) {
                            ?>
                                    <div class="col-lg-4 col-md-6 <?php echo getCSSAnimationClassAndStyle('animate__flipInX'); ?>">
                                        <div class="alert alert-danger">
                                            <i class="fas fa-times"></i> <?php
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
                                    <div class="col-lg-4 col-md-6 <?php echo getCSSAnimationClassAndStyle('animate__flipInX'); ?>">
                                        <div class="alert alert-success">
                                            <i class="fas fa-check"></i> <?php echo $value; ?>
                                        </div>
                                    </div>
                            <?php
                                }
                                if ($count % 2 === 0) {
                                    echo '<div class="clearfix visible-md"></div>';
                                }
                                if ($count % 3 === 0) {
                                    echo '<div class="clearfix visible-lg"></div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        PHP
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <?php
                            foreach ($messages['PHP'] as $value) {
                                if (is_array($value)) {
                            ?>
                                    <div class="col-sm-12 <?php echo getCSSAnimationClassAndStyle('animate__flipInX'); ?>">
                                        <div class="alert alert-danger">
                                            <i class="fas fa-times"></i> <?php
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
                                    <div class="col-sm-12 <?php echo getCSSAnimationClassAndStyle('animate__flipInX'); ?>">
                                        <div class="alert alert-success">
                                            <i class="fas fa-check"></i> <?php echo $value; ?>
                                        </div>
                                    </div>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Apache
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <?php
                            foreach ($messages['Apache'] as $value) {
                                if (is_array($value)) {
                            ?>
                                    <div class="col-sm-12 <?php echo getCSSAnimationClassAndStyle('animate__flipInX'); ?>">
                                        <div class="alert alert-danger">
                                            <i class="fas fa-times"></i> <?php
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
                                    <div class="col-sm-12 <?php echo getCSSAnimationClassAndStyle('animate__flipInX'); ?>">
                                        <div class="alert alert-success">
                                            <i class="fas fa-check"></i> <?php echo $value; ?>
                                        </div>
                                    </div>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>