<?php
/**
 * ---------------------------
 * Router helper class
 * ---------------------------
 *
 * This class is added so calls can be made statically like Router::get() making the code look pretty.
 * It also adds some extra functionality like default-namespace.
 */

namespace Pecee\SimpleRouter;

use Pecee\Exceptions\InvalidArgumentException;
use Pecee\Handlers\CallbackExceptionHandler;
use Pecee\Http\Middleware\BaseCsrfVerifier;
use Pecee\Http\Response;
use Pecee\SimpleRouter\Exceptions\HttpException;
use Pecee\SimpleRouter\Route\IRoute;
use Pecee\SimpleRouter\Route\RoutePartialGroup;
use Pecee\SimpleRouter\Route\RouteController;
use Pecee\SimpleRouter\Route\RouteGroup;
use Pecee\SimpleRouter\Route\RouteResource;
use Pecee\SimpleRouter\Route\RouteUrl;

class SimpleRouter
{
    /**
     * Default namespace added to all routes
     * @var string
     */
    protected static $defaultNamespace;

    /**
     * The response object
     * @var Response
     */
    protected static $response;

    /**
     * Router instance
     * @var Router
     */
    protected static $router;

    /**
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     * @throws HttpException
     * @throws \Exception
     */
    public static function start()
    {
        echo static::router()->routeRequest();
    }

    /**
     * Set default namespace which will be prepended to all routes.
     *
     * @param string $defaultNamespace
     */
    public static function setDefaultNamespace($defaultNamespace)
    {
        static::$defaultNamespace = $defaultNamespace;
    }

    /**
     * Base CSRF verifier
     *
     * @param BaseCsrfVerifier $baseCsrfVerifier
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function csrfVerifier(BaseCsrfVerifier $baseCsrfVerifier)
    {
        static::router()->setCsrfVerifier($baseCsrfVerifier);
    }

    /**
     * Boot managers allows you to alter the routes before the routing occurs.
     * Perfect if you want to load pretty-urls from a file or database.
     *
     * @param IRouterBootManager $bootManager
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function addBootManager(IRouterBootManager $bootManager)
    {
        static::router()->addBootManager($bootManager);
    }

    /**
     * Route the given url to your callback on GET request method.
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     *
     * @return RouteUrl
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function get($url, $callback, array $settings = null)
    {
        return static::match(['get'], $url, $callback, $settings);
    }

    /**
     * Route the given url to your callback on POST request method.
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @return RouteUrl
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function post($url, $callback, array $settings = null)
    {
        return static::match(['post'], $url, $callback, $settings);
    }

    /**
     * Route the given url to your callback on PUT request method.
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @return RouteUrl
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function put($url, $callback, array $settings = null)
    {
        return static::match(['put'], $url, $callback, $settings);
    }

    /**
     * Route the given url to your callback on PATCH request method.
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @return RouteUrl
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function patch($url, $callback, array $settings = null)
    {
        return static::match(['patch'], $url, $callback, $settings);
    }

    /**
     * Route the given url to your callback on OPTIONS request method.
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @return RouteUrl
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function options($url, $callback, array $settings = null)
    {
        return static::match(['options'], $url, $callback, $settings);
    }

    /**
     * Route the given url to your callback on DELETE request method.
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @return RouteUrl
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function delete($url, $callback, array $settings = null)
    {
        return static::match(['delete'], $url, $callback, $settings);
    }

    /**
     * Groups allows for encapsulating routes with special settings.
     *
     * @param array $settings
     * @param \Closure $callback
     * @return RouteGroup
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     * @throws InvalidArgumentException
     */
    public static function group(array $settings = [], \Closure $callback)
    {
        if (is_callable($callback) === false) {
            throw new InvalidArgumentException('Invalid callback provided. Only functions or methods supported');
        }

        $group = new RouteGroup();
        $group->setCallback($callback);
        $group->setSettings($settings);

        static::router()->addRoute($group);

        return $group;
    }

    /**
     * Special group that has the same benefits as group but supports
     * parameters and which are only rendered when the url matches.
     *
     * @param string $url
     * @param \Closure $callback
     * @param array $settings
     * @return RoutePartialGroup
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     * @throws InvalidArgumentException
     */
    public static function partialGroup($url, \Closure $callback, array $settings = [])
    {
        if (is_callable($callback) === false) {
            throw new InvalidArgumentException('Invalid callback provided. Only functions or methods supported');
        }

        $settings['prefix'] = $url;

        $group = new RoutePartialGroup();
        $group->setSettings($settings);
        $group->setCallback($callback);

        static::router()->addRoute($group);

        return $group;
    }

    /**
     * Alias for the form method
     *
     * @param string $url
     * @param callable $callback
     * @param array|null $settings
     * @see SimpleRouter::form
     * @return RouteUrl
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function basic($url, $callback, array $settings = null)
    {
        return static::match(['get', 'post'], $url, $callback, $settings);
    }

    /**
     * This type will route the given url to your callback on the provided request methods.
     * Route the given url to your callback on POST and GET request method.
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @see SimpleRouter::form
     * @return RouteUrl
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function form($url, $callback, array $settings = null)
    {
        return static::match(['get', 'post'], $url, $callback, $settings);
    }

    /**
     * This type will route the given url to your callback on the provided request methods.
     *
     * @param array $requestMethods
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @return RouteUrl|IRoute
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function match(array $requestMethods, $url, $callback, array $settings = null)
    {
        $route = new RouteUrl($url, $callback);
        $route->setRequestMethods($requestMethods);
        $route = static::addDefaultNamespace($route);

        if ($settings !== null) {
            $route->setSettings($settings);
        }

        static::router()->addRoute($route);

        return $route;
    }

    /**
     * This type will route the given url to your callback and allow any type of request method
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @return RouteUrl|IRoute
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function all($url, $callback, array $settings = null)
    {
        $route = new RouteUrl($url, $callback);
        $route = static::addDefaultNamespace($route);

        if ($settings !== null) {
            $route->setSettings($settings);
        }

        static::router()->addRoute($route);

        return $route;
    }

    /**
     * This route will route request from the given url to the controller.
     *
     * @param string $url
     * @param string $controller
     * @param array|null $settings
     * @return RouteController|IRoute
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function controller($url, $controller, array $settings = null)
    {
        $route = new RouteController($url, $controller);
        $route = static::addDefaultNamespace($route);

        if ($settings !== null) {
            $route->setSettings($settings);
        }

        static::router()->addRoute($route);

        return $route;
    }

    /**
     * This type will route all REST-supported requests to different methods in the provided controller.
     *
     * @param string $url
     * @param string $controller
     * @param array|null $settings
     * @return RouteResource|IRoute
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function resource($url, $controller, array $settings = null)
    {
        $route = new RouteResource($url, $controller);
        $route = static::addDefaultNamespace($route);

        if ($settings !== null) {
            $route->setSettings($settings);
        }

        static::router()->addRoute($route);

        return $route;
    }

    /**
     * Add exception callback handler.
     *
     * @param \Closure $callback
     * @return CallbackExceptionHandler $callbackHandler
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function error(\Closure $callback)
    {
        $routes = static::router()->getRoutes();

        $callbackHandler = new CallbackExceptionHandler($callback);

        $group = new RouteGroup();
        $group->addExceptionHandler($callbackHandler);

        array_unshift($routes, $group);

        static::router()->setRoutes($routes);

        return $callbackHandler;
    }

    /**
     * Get url for a route by using either name/alias, class or method name.
     *
     * The name parameter supports the following values:
     * - Route name
     * - Controller/resource name (with or without method)
     * - Controller class name
     *
     * When searching for controller/resource by name, you can use this syntax "route.name@method".
     * You can also use the same syntax when searching for a specific controller-class "MyController@home".
     * If no arguments is specified, it will return the url for the current loaded route.
     *
     * @param string|null $name
     * @param string|array|null $parameters
     * @param array|null $getParams
     * @throws \Pecee\Exceptions\InvalidArgumentException
     * @return string
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function getUrl($name = null, $parameters = null, $getParams = null)
    {
        return static::router()->getUrl($name, $parameters, $getParams);
    }

    /**
     * Get the request
     *
     * @return \Pecee\Http\Request
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function request()
    {
        return static::router()->getRequest();
    }

    /**
     * Get the response object
     *
     * @return Response
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function response()
    {
        if (static::$response === null) {
            static::$response = new Response(static::request());
        }

        return static::$response;
    }

    /**
     * Returns the router instance
     *
     * @return Router
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public static function router()
    {
        if (static::$router === null) {
            static::$router = new Router();
        }

        return static::$router;
    }

    /**
     * Prepends the default namespace to all new routes added.
     *
     * @param IRoute $route
     * @return IRoute
     */
    public static function addDefaultNamespace(IRoute $route)
    {
        if (static::$defaultNamespace !== null) {

            $callback = $route->getCallback();

            /* Only add default namespace on relative callbacks */
            if ($callback === null || (is_string($callback) === true && $callback[0] !== '\\')) {

                $namespace = static::$defaultNamespace;

                $currentNamespace = $route->getNamespace();

                if ($currentNamespace !== null) {
                    $namespace .= '\\' . $currentNamespace;
                }

                $route->setDefaultNamespace($namespace);

            }
        }

        return $route;
    }

    /**
     * Get default namespace
     * @return string
     */
    public static function getDefaultNamespace()
    {
        return static::$defaultNamespace;
    }

}