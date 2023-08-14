<?php 
$url = $_REQUEST['url'];

$scheme = parse_url(trim($url), PHP_URL_SCHEME);
$host = parse_url(trim($url), PHP_URL_HOST);
$domain = strtolower($scheme."://".$host.'/');

$ch = curl_init(); 

curl_setopt($ch, CURLOPT_HEADER, 0); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_URL, $domain."info"); 

$data = json_decode(curl_exec($ch)); 

curl_close($ch); 
if (isset($data->url) && $data->url == $domain) {
    echo $domain;
} else {
    echo "";
}