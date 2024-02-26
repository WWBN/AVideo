<?php

$defaultLines = 500;
$defaultTimeout = 1;
$maxRepeatTimes = 5;

$linesCount = $defaultLines;
$timeout = $defaultTimeout;

$apacheAccessLogFile = '/var/log/apache2/access.log';

function tailShell($filepath, $lines = 1) {
    ob_start();
    passthru('tail -' . $lines . ' ' . escapeshellarg($filepath));
    return preg_split("/\r\n|\n|\r/", trim(ob_get_clean()));
}

function percentloadavg(){
    $cpu_count = 1;
    if(is_file('/proc/cpuinfo')) {
        $cpuinfo = file_get_contents('/proc/cpuinfo');
        preg_match_all('/^processor/m', $cpuinfo, $matches);
        $cpu_count = count($matches[0]);
    }

    $sys_getloadavg = sys_getloadavg();
    $sys_getloadavg[0] = $sys_getloadavg[0] / $cpu_count;
    $sys_getloadavg[1] = $sys_getloadavg[1] / $cpu_count;
    $sys_getloadavg[2] = $sys_getloadavg[2] / $cpu_count;

    return $sys_getloadavg;
}

$ips = array();
$uas = array();
$ipsProcessed = array();
$mySQLIsStopped = 0;
while (1) {

    $lines = tailShell($apacheAccessLogFile, $linesCount);
    //file_put_contents($apacheAccessLogFile,'');

    $linesTime = array();

    foreach ($lines as $line) {
        $key = base64_encode($line);
        if(!isset($linesTime[$key])){
            $linesTime[$key] = 0;
        }else{
            $linesTime[$key]++;
        }
    }

    $total = count($linesTime);
    $newRules = array();

    foreach ($linesTime as $key => $repeat) {
        if($repeat<$maxRepeatTimes){
            continue;
        }
        $line = base64_decode($key);
        preg_match('/^([0-9.]+).*Windows NT [56]/i', $line, $matches);
        if (empty($matches[1])) {
            continue;
        }
        $ip = trim($matches[1]);
        $cmd = 'sudo ufw insert 1 deny from ' . $ip . '  to any' . PHP_EOL;
        echo "{$key}/{$total} " . $cmd;
        echo $line. PHP_EOL;
        $output = null;
        exec($cmd . ' 2>&1', $output, $return_var);
        echo json_encode($output) . PHP_EOL;
        echo '--------------' . PHP_EOL;
        if ($output[0] === 'Rule inserted') {
            $newRules[] = $ip;
        }
        $ipsProcessed[] = $ip;
    }
    $totalNew = count($newRules);
    //echo PHP_EOL . date('Y-m-d H:i:s').' Found ' . $total . PHP_EOL;
    //echo PHP_EOL . $totalNew . ' New IPs added: ' . implode(', ', $newRules) . PHP_EOL;

    if ($totalNew) {
        $timeout = $defaultTimeout/4;
        $linesCount = intval($defaultLines/4);
        echo "*** {$totalNew} new rules inserted" . PHP_EOL;
    }
    
    if ($totalNew && !$mySQLIsStopped) {
        $load = percentloadavg();
        echo '*** sys_getloadavg: '.$load[0] . PHP_EOL;
        if ($load[0] > 0.80) {
            //exec('/etc/init.d/apache2 restart');
            echo '*** STOP MySQL' . PHP_EOL;
            $mySQLIsStopped = 1;
            exec('/etc/init.d/mysql stop');
        }
    } else if (empty($totalNew) && $mySQLIsStopped) {
        echo '*** Start MySQL 1' . PHP_EOL;
        $mySQLIsStopped = 0;
        exec('/etc/init.d/mysql start');
        $timeout = $defaultTimeout;
        $linesCount = $defaultLines;
    }else if($mySQLIsStopped){
        $load = percentloadavg();
        echo '*** sys_getloadavg: '.$load[0] . PHP_EOL;
        if ($load[0] < 0.50) {
            echo '*** Start MySQL 2' . PHP_EOL;
            $mySQLIsStopped = 0;
            exec('/etc/init.d/mysql start');
            $timeout = $defaultTimeout;
            $linesCount = $defaultLines;
        }
    }
    if ($totalNew) {
        echo "*******" . PHP_EOL . PHP_EOL;
    }
    sleep($timeout);
}