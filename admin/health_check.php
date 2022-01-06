<?php
function _isAPPInstalled($appName)
{
    $appName = preg_replace('/[^a-z0-9_-]/i', '', $appName);
    return trim(shell_exec("which {$appName}"));
}

$phpExtensions = [];
$phpExtensions[] = ['pdo_mysql'];
$phpExtensions[] = ['curl'];
$phpExtensions[] = ['gd', 'Important to generate images'];
$phpExtensions[] = ['xml', 'Important to get the live stats'];
$phpExtensions[] = ['zip', 'Important handle HLS files'];
// $phpExtensions[] = array('mbstring'); // I could not detect that

$apacheModules = [];
$apacheModules[] = ['mod_php'];
$apacheModules[] = ['mod_xsendfile', 'https://github.com/WWBN/AVideo/wiki/Install-Apache-XSendFIle'];
$apacheModules[] = ['mod_rewrite'];
$apacheModules[] = ['mod_expires', 'Important for CDN and cache configuration'];
$apacheModules[] = ['mod_headers', 'Important for CDN and cache configuration'];

$linuxApps = [];
$linuxApps[] = ['mysql'];
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


if (isset($_SERVER["HTTPS"])) {
    $messages['Apache'][] = "HTTPS is enabled";
} else {
    $messages['Apache'][] = ["HTTPS is not enabled", 'https://github.com/WWBN/AVideo/wiki/Why-use-HTTPS'];
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

if (is_writable($global['logfile'])) {
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


$verifyURL = "https://search.avideo.com/verify.php";
$verifyURL = addQueryStringParameter($verifyURL, 'url', $global['webSiteRootURL']);
$verifyURL = addQueryStringParameter($verifyURL, 'screenshot', 1);

$result = url_get_contents($verifyURL, '', 5);
if (empty($result)) {
    $messages['Server'][] = ["We could not verify your server from outside {$global['webSiteRootURL']}"];
} else {
    $verified = json_decode($result);
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
?>
<style>
    #healthCheck .alert{
        overflow: auto;
    }
</style>
<div class="panel panel-default" id="healthCheck">
    <div class="panel-heading">
        <?php echo '<h1>' . PHP_OS . '</h1>'; ?>
    </div>
    <div class="panel-body">

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
                                    } ?>
                                        </div>    
                                    </div>
                                    <?php
                                } else {
                                    ?>
                                    <div class="col-lg-4 col-md-6 <?php echo getCSSAnimationClassAndStyle('animate__flipInX'); ?>">
                                        <div class="alert alert-success">
                                            <i class="fas fa-check"></i> <?php
                                            echo $value; ?>
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
                                    } ?>
                                        </div>    
                                    </div>
                                    <?php
                                } else {
                                    ?>
                                    <div class="col-sm-12 <?php echo getCSSAnimationClassAndStyle('animate__flipInX'); ?>">
                                        <div class="alert alert-success">
                                            <i class="fas fa-check"></i> <?php
                                            echo $value; ?>
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
                                    } ?>
                                        </div>    
                                    </div>
                                    <?php
                                } else {
                                    ?>
                                    <div class="col-sm-12 <?php echo getCSSAnimationClassAndStyle('animate__flipInX'); ?>">
                                        <div class="alert alert-success">
                                            <i class="fas fa-check"></i> <?php
                                            echo $value; ?>
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