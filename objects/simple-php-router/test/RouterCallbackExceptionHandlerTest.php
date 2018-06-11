<?php

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once 'Dummy/Exceptions/ExceptionHandlerException.php';
require_once 'Helpers/TestRouter.php';

class RouterCallbackExceptionHandlerTest extends PHPUnit_Framework_TestCase
{

    public function testCallbackExceptionHandler()
    {
        $this->setExpectedException(ExceptionHandlerException::class);

        // Match normal route on alias
        TestRouter::get('/my-new-url', 'DummyController@method2');
        TestRouter::get('/my-url', 'DummyController@method1');

        TestRouter::error(function (\Pecee\Http\Request $request, \Exception $exception) {
            throw new ExceptionHandlerException();
        });

        TestRouter::debugNoReset('/404-url', 'get');
        TestRouter::router()->reset();
    }

}