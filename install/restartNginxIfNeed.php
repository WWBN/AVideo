<?php
if(php_sapi_name() !== 'cli'){
    die('command line only');
}

/**
 * Detects if an SSL certificate is valid for the given hostname and port.
 *
 * @param string $hostname The hostname to connect to.
 * @param int $port The port number to connect to.
 * @param int $timeout The timeout value in seconds (default: 30).
 * @return bool Returns true if the SSL certificate is valid, false otherwise.
 */
function is_ssl_certificate_valid($hostname, $port = 8443, $timeout = 30) {
    $errno = '';
    $errstr = '';
    $context = stream_context_create(array("ssl" =>
        array(
            "capture_peer_cert" => true,
            'verify_peer'=>false,
            'verify_peer_name'=>false,
            'allow_self_signed'=>true
        )
    ));
    $stream = @stream_socket_client("ssl://{$hostname}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
    if(!empty($stream)){
        $cert_data = openssl_x509_parse(stream_context_get_params($stream)["options"]["ssl"]["peer_certificate"]);

        $validFrom = DateTime::createFromFormat('ymdHisT', $cert_data["validFrom"]);
        $validTo = DateTime::createFromFormat('ymdHisT', $cert_data["validTo"]);
        $now = new DateTime();

        if ($now >= $validFrom && $now <= $validTo) {
            return true;
        } else {
            return false;
        }
    } else {
        // Error connecting to the SSL endpoint
        error_log("Failed to connect to SSL endpoint: {$errstr} ({$errno})");
        return false;
    }
}

function isDocker(){
    return file_exists('/var/www/docker_vars.json');
}

$hostname = 'live';
if(!isDocker()){
    $hostname = 'localhost';
}

if (!is_ssl_certificate_valid($hostname)) {
    // Restart Nginx
    echo 'Restart Nginx';
    exec('/usr/local/nginx/sbin/nginx -s stop');
    sleep(3);
    exec('/usr/local/nginx/sbin/nginx');
}else{
    echo 'No need to restart';
}