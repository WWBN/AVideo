<?php

namespace Vimeo\Upload;

use TusPhp\Tus\Client;

class TusClientFactory
{
    /**
     * @param string $base_uri The fully qualified domain of the upload, ex: https://us-files.tus.vimeo.com
     * @param string $url The fully qualified url of the upload, ex: https://us-files.tus.vimeo.com/files/vimeo-a1b2c3d4
     * @return Client
     */
    public function getTusClient(string $base_uri, string $url) : Client
    {
        $client = new TusClient($base_uri);
        $client->setUrl($url);
        return $client;
    }
}