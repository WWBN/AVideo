<?php
$localServer = false;
$webRTCServerURL = 'https://ubuntu.gdrive.local/liveStandalone/WebRTCServer/';
$OME_HLS_STREAM_PORT = 7770;
$OME_API_PORT = 7771;
$OME_SOCKET_PORT = 7772;
$OME_STREAM_PORT_TLS = 7773;
$OME_TCP_RELAY_ADDRESS = 7774;
$OME_ICE_CANDIDATES = '7775-7779';
$AccessToken = 'mysecret';
$pushRTMP = false;

$files = array(
    'CertPath'=>'/etc/ssl/certs/apache-selfsigned.crt', 
    'KeyPath'=>'/etc/ssl/private/apache-selfsigned.key', 
    'ChainCertPath'=>'/etc/ssl/certs/dhparam2.pem'
);
