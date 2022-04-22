<?php

function tailShell($filepath, $lines = 1) {
    ob_start();
    passthru('tail -'  . $lines . ' ' . escapeshellarg($filepath));
    return preg_split("/\r\n|\n|\r/", trim(ob_get_clean()));
}

$lines = tailShell('/var/log/apache2/access.log', 1000);

$ips = array();

foreach($lines as $line){
    preg_match('/^([0-9.]+).*X11; Linux/i', $line, $matches);
    if(!empty($matches[1])){
        $ip = trim($matches[1]);
        if(!in_array($ip, $ips)){
            $ips[] = $ip;
        }

    }
}

foreach($ips as $ip){
    $cmd = 'sudo ufw insert 1 deny from '.$ip.'  to any'.PHP_EOL;
    echo $cmd;
    $output = null;
    exec($cmd.' 2>&1', $output, $return_var);
    echo json_encode($output).PHP_EOL;
}

echo PHP_EOL.'Found '.count($ips).PHP_EOL;