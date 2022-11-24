<?php


namespace Vimeo\Upload;



class TusClient extends \TusPhp\Tus\Client
{
    /**
     * Sets the url for retrieving the TUS upload.
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
        return $this;
    }
}