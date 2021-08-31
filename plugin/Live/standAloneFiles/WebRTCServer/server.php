<?php

require_once dirname(__FILE__) . '/functions.php';

$certificateFolderName = "/YPTcertificates/{$ServerHost}";
make_path($certificateFolderName);

$imageName = "ovenmediaengine-{$ServerHost}";

// create a folder to store the certificates
if (!is_dir($certificateFolderName)) {
    $command = 'mkdir ' . $certificateFolderName;
    exec($command);
}

$command = "rm {$certificateFolderName}/*.pem";
exec($command);

foreach ($files as $key => $value) {
    // copy all certificates into the folder
    if (!file_exists($value)) {
        echo ('WebRTCLiveCam server ERROR file does not exists ' . $value . PHP_EOL);
    } else {
        $command = "cp -Lr {$value} {$certificateFolderName}/{$key}.pem";
        echo ($command . PHP_EOL);
        exec($command);
    }
}

$command = 'docker stop ' . $imageName . ' && docker rm ' . $imageName;
exec($command);

$path = '/var/lib/docker/volumes/ome-origin-conf/_data/';
make_path($path);
$ServerXML = $path . 'Server.xml';
$LoggerXML = $path . 'Logger.xml';

if (file_exists($ServerXML)) {
    $command = 'rm ' . $ServerXML;
    exec($command);
}

if (!file_exists($LoggerXML)) {
    $command = 'cp ' . dirname(__FILE__) . '/Logger.xml ' . $LoggerXML;
    exec($command);
}


$content = file_get_contents(dirname(__FILE__) . '/Server.xml');

$search = array('{ServerHost}', '{AccessToken}', '{OME_API_PORT}');
$replace = array($ServerHost, $AccessToken, $OME_API_PORT);

file_put_contents($ServerXML, str_replace($search, $replace, $content));

$command = 'docker run -d '
        . "-p {$OME_API_PORT}:{$OME_API_PORT} "
        //. "-p 41935:41935 "
        //. "-p 3333:3333 "
        . "-p {$OME_SOCKET_PORT}:{$OME_SOCKET_PORT} "
        . "-p {$OME_TCP_RELAY_ADDRESS}:{$OME_TCP_RELAY_ADDRESS} "
        . "-p {$OME_HLS_STREAM_PORT}:{$OME_HLS_STREAM_PORT} "
        //. "-p 20081:20081 "  //Thumbnail
        . "-p {$OME_STREAM_PORT_TLS}:{$OME_STREAM_PORT_TLS} "
        //. "-p 9000:9000 "
        //. "-p 9999:9999/udp "
        //. "-p 4000-4005:4000-4005/udp "
        . "-p {$OME_ICE_CANDIDATES}:{$OME_ICE_CANDIDATES}/udp "
        . "--env OME_API_PORT={$OME_API_PORT} "
        . "--env OME_STREAM_PORT_TLS={$OME_STREAM_PORT_TLS} "
        . "--env OME_HLS_STREAM_PORT={$OME_HLS_STREAM_PORT} "
        . "--env OME_SOCKET_PORT={$OME_SOCKET_PORT} "
        . "--env OME_TCP_RELAY_ADDRESS='*:{$OME_TCP_RELAY_ADDRESS}' "
        . "--env OME_ICE_CANDIDATES='*:{$OME_ICE_CANDIDATES}/udp' "
        . '-v ome-origin-conf:/opt/ovenmediaengine/bin/origin_conf '
        . '-v ome-edge-conf:/opt/ovenmediaengine/bin/edge_conf '
        . '-v ' . $certificateFolderName . ':/cert '
        //. '--name '.$imageName.' youphptube/ovenmediaengine:v1';
        . '--name ' . $imageName . ' airensoft/ovenmediaengine:latest';

echo ('Execute WebRTCLiveCam server: ' . PHP_EOL . $command . PHP_EOL);

exec($command);
