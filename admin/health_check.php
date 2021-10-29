<?php

function _isAPPInstalled($appName) {
    $appName = preg_replace('/[^a-z0-9_-]/i', '', $appName);
    return trim(shell_exec("which {$appName}"));
}

$phpExtensions = array();
$phpExtensions[] = array('pdo_mysql');
$phpExtensions[] = array('curl');
$phpExtensions[] = array('gd');
$phpExtensions[] = array('xml');
$phpExtensions[] = array('zip');
$phpExtensions[] = array('mbstring');

$apacheModules = array();
$apacheModules[] = array('mod_php');
$apacheModules[] = array('mod_xsendfile', 'https://github.com/WWBN/AVideo/wiki/Install-Apache-XSendFIle');
$apacheModules[] = array('mod_rewrite');
$apacheModules[] = array('mod_expires');
$apacheModules[] = array('mod_headers');

$linuxApps = array();
$linuxApps[] = array('mysql');
$linuxApps[] = array('ffmpeg');
$linuxApps[] = array('git');
$linuxApps[] = array('exiftool');
$linuxApps[] = array('unzip');
$linuxApps[] = array('youtube-dl');
$linuxApps[] = array('sshpass');
$linuxApps[] = array('apache2');


$messages = array();
$version = phpversion();
$phpMinVersion = '7.3.0';
if (strnatcmp($version, $phpMinVersion) >= 0) {
    $messages[] = "PHP v{$version}";
} else {
    $messages[] = "PHP v{$version}, please upgrade to version {$phpMinVersion} or greater";
}

if (isset($_SERVER["HTTPS"])) {
    $messages[] = "HTTPS is enabled";
} else {
    $messages[] = array("HTTPS is not enabled", 'https://github.com/WWBN/AVideo/wiki/Why-use-HTTPS');
}

$extensions = array_map('strtolower', get_loaded_extensions());
//var_dump($extensions);
foreach ($phpExtensions as $value) {
    if (in_array($value[0], $extensions)) {
        $messages[] = $value[0];
    } else {
        $messages[] = array($value[0], 'sudo apt-get install php-'. str_replace('_', '-', $value[0]).' -y && sudo /etc/init.d/apache2 restart');
    }
}

$mods = array_map('strtolower', apache_get_modules());
//var_dump($mods);
foreach ($apacheModules as $value) {
    if (in_array($value[0], $mods)) {
        $messages[] = $value[0];
    } else {
        $messages[] = array($value[0], @$value[1]);
    }
}

foreach ($linuxApps as $value) {
    $response = _isAPPInstalled($value[0]);
    if (!empty($response)) {
        $messages[] = "{$value[0]} is installed here {$response}";
    } else {
        $messages[] = array("{$value[0]} is NOT installed", @$value[1]);
    }
}
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo '<h1>' . PHP_OS . '</h1>'; ?>
    </div>
    <div class="panel-body">
        <div class="row">
            <?php
            foreach ($messages as $value) {
                if (is_array($value)) {
                    ?>
                    <div class="col-sm-3">
                        <div class="alert alert-danger">
                            <i class="fas fa-times"></i> <?php
                            echo $value[0];
                            if (!empty($value[1])) {
                                if(preg_match('/^http/i', $value[1])){
                                ?>
                                <a href="<?php echo $value[1]; ?>" class="btn btn-danger btn-xs pull-right" target="_blank"><i class="fas fa-hand-holding-medical"></i> </a> 
                                <?php
                                }else{
                                ?>
                                <code><?php echo $value[1]; ?></code> 
                                <?php
                                }
                            }
                            ?>
                        </div>    
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="col-sm-3">
                        <div class="alert alert-success">
                            <i class="fas fa-check"></i> <?php
                            echo $value;
                            ?>
                        </div>  
                    </div>    
                    <?php
                }
            }
            ?>
        </div>
    </div>
</div>