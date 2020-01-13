<?php

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once 'Helpers/TestRouter.php';

class GroupTest extends PHPUnit_Framework_TestCase
{
    protected $result;

    public function testGroupLoad()
    {
        $this->result = false;

        TestRouter::group(['prefix' => '/group'], function () {
            $this->result = true;
        });

        try {
            TestRouter::debug('/', 'get');
        } catch(\Exception $e) {

        }
        $this->assertTrue($this->result);
    }

    public function testNestedGroup()
    {

        TestRouter::group(['prefix' => '/api'], function () {

            TestRouter::group(['prefix' => '/v1'], function () {
                TestRouter::get('/test', 'DummyController@method1');
            });

        });

        TestRouter::debug('/api/v1/test', 'get');

    }

    public function testMultipleRoutes()
    {

        TestRouter::group(['prefix' => '/api'], function () {

            TestRouter::group(['prefix' => '/v1'], function () {
                TestRouter::get('/test', 'DummyController@method1');
            });

        });

        TestRouter::get('/my/match', 'DummyController@method1');

        TestRouter::group(['prefix' => '/service'], function () {

            TestRouter::group(['prefix' => '/v1'], function () {
                TestRouter::get('/no-match', 'DummyController@method1');
            });

        });

        TestRouter::debug('/my/match', 'get');
    }

    public function testUrls()
    {
        // Test array name
        TestRouter::get('/my/fancy/url/1', 'DummyController@method1', ['as' => 'fancy1']);

        // Test method name
        TestRouter::get('/my/fancy/url/2', 'DummyController@method1')->setName('fancy2');

        TestRouter::debugNoReset('/my/fancy/url/1');

        $this->assertEquals('/my/fancy/url/1/', TestRouter::getUrl('fancy1'));
        $this->assertEquals('/my/fancy/url/2/', TestRouter::getUrl('fancy2'));

        TestRouter::router()->reset();

    }

}