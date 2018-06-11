<?php

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once 'Dummy/Handler/ExceptionHandler.php';
require_once 'Helpers/TestRouter.php';

class MiddlewareTest extends PHPUnit_Framework_TestCase
{
    public function testMiddlewareFound()
    {
        $this->setExpectedException(MiddlewareLoadedException::class);

        TestRouter::group(['exceptionHandler' => 'ExceptionHandler'], function () {
            TestRouter::get('/my/test/url', 'DummyController@method1', ['middleware' => 'DummyMiddleware']);
        });

        TestRouter::debug('/my/test/url', 'get');

    }

    public function testNestedMiddlewareDontLoad()
    {

        TestRouter::group(['exceptionHandler' => 'ExceptionHandler', 'middleware' => 'DummyMiddleware'], function () {
            TestRouter::get('/middleware', 'DummyController@method1');
        });

        TestRouter::get('/my/test/url', 'DummyController@method1');

        TestRouter::debug('/my/test/url', 'get');
    }

}