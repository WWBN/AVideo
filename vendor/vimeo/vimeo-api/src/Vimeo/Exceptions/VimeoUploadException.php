<?php
namespace Vimeo\Exceptions;

/**
 * VimeoUploadException class for failure to upload to the server.
 */
class VimeoUploadException extends \Exception implements ExceptionInterface
{
    protected bool $retryable;

    public function __construct(string $message = '', int $code = 0, bool $retryable = false)
    {
        parent::__construct($message, $code);
        $this->retryable = $retryable;
    }

    public function isRetryable()
    {
        return $this->retryable;
    }
}
