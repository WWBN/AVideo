<?php
if (version_compare(PHP_VERSION, '7.2') < 0){
  $msg = array();
  $msg[] = 'You are runing PHP version: '.PHP_VERSION;
  $msg[] = 'Please Update your PHP version to 7.2 or above. (7.3 is recommended)';
  $msg[] = '<h5>For Ubuntu 16</h5>sudo add-apt-repository ppa:jczaplicki/xenial-php74-temp';
  $msg[] = 'sudo apt-get update && sudo apt-get upgrade';
  $msg[] = 'sudo apt-get install php7.4 libapache2-mod-php7.4 php7.4-mysql php7.4-curl php7.4-gd php7.4-intl php7.4-zip php7.4-xml -y';
  $msg[] = 'sudo update-alternatives --set php /usr/bin/php7.4 && sudo a2dismod php7.0 && sudo a2enmod php7.4';
  $msg[] = 'sudo /etc/init.d/apache2 restart';
  die(implode('<br>', $msg));
}

global $global, $config;
$configFile = '../videos/configuration.php';
if(!isset($global['systemRootPath'])){
    if (!file_exists($configFile)) {
        if (!file_exists('../install/index.php')) {
            forbiddenPage("No Configuration and no Installation");
          }
          header("Location: install/index.php");
          exit;
    } else {
      require_once '../videos/configuration.php';
    }
}
if (!empty($global['systemRootPath']) && empty($config)) {
    // update config file for version 2.8
    $txt = 'require_once $global[\'systemRootPath\'].\'objects/include_config.php\';';
    $myfile = file_put_contents($configFile, $txt . PHP_EOL, FILE_APPEND | LOCK_EX);
    require_once $global['systemRootPath'].'objects/include_config.php';
}else if(empty ($global['systemRootPath'])){
    die("Error to find systemRootPath = ({$global['systemRootPath']})");
    error_log(json_encode($global));
}

    
if(!empty($_GET['playlist_name']) && empty($_GET['playlist_id'])){
    if ($_GET['playlist_name'] == "favorite") {
        $_GET['playlist_id'] = 'favorite';
    } else {
        $_GET['playlist_id'] = 'watch-later';
    }
}
require_once $global['systemRootPath'].'plugin/AVideoPlugin.php';
$firstPage = AVideoPlugin::getFirstPage();
if (empty($firstPage) || !empty($_GET['videoName']) || !empty($_GET['v']) || !empty($_GET['playlist_id']) || !empty($_GET['liveVideoName']) || !empty($_GET['evideo'])) {
    require $global['systemRootPath'].'view/modeYoutube.php';
}else{
    require $firstPage;
}
include $global['systemRootPath'].'objects/include_end.php';
