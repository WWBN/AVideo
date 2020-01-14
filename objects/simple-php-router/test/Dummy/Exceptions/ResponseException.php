<?php

class ResponseException extends \Exception
{
    protected $response;

    public function __construct($response)
    {
        $this->response = $response;
        parent::__construct('', 0);
    }

    public function getResponse()
    {
        return $this->response;
    }

}