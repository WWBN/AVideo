<?php
require_once 'Dummy/DummyController.php';
require_once 'Dummy/Exceptions/ResponseException.php';
require_once 'Dummy/Handler/ExceptionHandlerFirst.php';
require_once 'Dummy/Handler/ExceptionHandlerSecond.php';
require_once 'Dummy/Handler/ExceptionHandlerThird.php';
require_once 'Helpers/TestRouter.php';
require_once 'Dummy/Middlewares/RewriteMiddleware.php';

class RouteRewriteTest extends PHPUnit_Framework_TestCase
{

    /**
     * Redirects to another route through 3 exception handlers.
     *
     * You will see "ExceptionHandler 1 loaded" 2 times. This happen because
     * the exceptionhandler is asking the router to reload.
     *
     * That means that the exceptionhandler is loaded again, but this time
     * the router ignores the same rewrite-route to avoid loop - loads
     * the second which have same behavior and is also ignored before
     * throwing the final Exception in ExceptionHandler 3.
     *
     * So this tests:
     * 1. If ExceptionHandlers loads
     * 2. If ExceptionHandlers load in the correct order
     * 3. If ExceptionHandlers can rewrite the page on error
     * 4. If the router can avoid redirect-loop due to developer has started loop.
     * 5. And finally if we reaches the last exception-handler and that the correct
     *    exception-type is being thrown.
     */
    public function testExceptionHandlerRewrite()
    {
        global $stack;
        $stack = [];

        TestRouter::group(['exceptionHandler' => [ExceptionHandlerFirst::class, ExceptionHandlerSecond::class]], function () use ($stack) {

            TestRouter::group(['exceptionHandler' => ExceptionHandlerThird::class], function () use ($stack) {

                TestRouter::get('/my-path', 'DummyController@method1');

            });
        });

        try {
            TestRouter::debug('/my-non-existing-path', 'get');
        } catch (\ResponseException $e) {

        }

        $expectedStack = [
            ExceptionHandlerFirst::class,
            ExceptionHandlerSecond::class,
            ExceptionHandlerThird::class,
        ];

        $this->assertEquals($expectedStack, $stack);

    }

    public function testRewriteExceptionMessage()
    {
        $this->setExpectedException(\Pecee\SimpleRouter\Exceptions\NotFoundHttpException::class);

        TestRouter::error(function (\Pecee\Http\Request $request, \Exception $error) {

            if (strtolower($request->getUrl()->getPath()) === '/my/test/') {
                $request->setRewriteUrl('/another-non-existing');
            }

        });

        TestRouter::debug('/my/test', 'get');
    }

    public function testRewriteUrlFromRoute()
    {

        TestRouter::get('/old', function () {
            TestRouter::request()->setRewriteUrl('/new');
        });

        TestRouter::get('/new', function () {
            echo 'ok';
        });

        TestRouter::get('/new1', function () {
            echo 'ok';
        });

        TestRouter::get('/new2', function () {
            echo 'ok';
        });

        $output = TestRouter::debugOutput('/old');

        $this->assertEquals('ok', $output);

    }

    public function testRewriteCallbackFromRoute()
    {

        TestRouter::get('/old', function () {
            TestRouter::request()->setRewriteUrl('/new');
        });

        TestRouter::get('/new', function () {
            return 'ok';
        });

        TestRouter::get('/new1', function () {
            return 'fail';
        });

        TestRouter::get('/new/2', function () {
            return 'fail';
        });

        $output = TestRouter::debugOutput('/old');

        TestRouter::router()->reset();

        $this->assertEquals('ok', $output);

    }

    public function testRewriteRouteFromRoute()
    {

        TestRouter::get('/match', function () {
            TestRouter::request()->setRewriteRoute(new \Pecee\SimpleRouter\Route\RouteUrl('/match', function () {
                return 'ok';
            }));
        });

        TestRouter::get('/old1', function () {
            return 'fail';
        });

        TestRouter::get('/old/2', function () {
            return 'fail';
        });

        TestRouter::get('/new2', function () {
            return 'fail';
        });

        $output = TestRouter::debugOutput('/match');

        TestRouter::router()->reset();

        $this->assertEquals('ok', $output);

    }

    public function testMiddlewareRewrite()
    {

        TestRouter::group(['middleware' => 'RewriteMiddleware'], function () {
            TestRouter::get('/', function () {
                return 'fail';
            });

            TestRouter::get('no/match', function () {
                return 'fail';
            });
        });

        $output = TestRouter::debugOutput('/');

        $this->assertEquals('ok', $output);

    }

}