<?php

namespace Pecee\Handlers;

use Pecee\Http\Request;

/**
 * Class CallbackExceptionHandler
 *
 * Class is used to create callbacks which are fired when an exception is reached.
 * This allows for easy handling 404-exception etc. without creating an custom ExceptionHandler.
 *
 * @package Pecee\Handlers
 */
class CallbackExceptionHandler implements IExceptionHandler
{

    protected $callback;

    public function __construct(\Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param Request $request
     * @param \Exception $error
     * @return Request|null
     */
    public function handleError(Request $request, \Exception $error)
    {
        /* Fire exceptions */
        return call_user_func($this->callback,
            $request,
            $error
        );
    }
}