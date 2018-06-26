<?php

namespace Pecee\SimpleRouter\Route;

use Pecee\Http\Request;

interface IRoute
{
    /**
     * Method called to check if a domain matches
     *
     * @param string $route
     * @param Request $request
     * @return bool
     */
    public function matchRoute($route, Request $request);

    /**
     * Called when route is matched.
     * Returns class to be rendered.
     *
     * @param Request $request
     * @throws \Pecee\SimpleRouter\Exceptions\NotFoundHttpException
     * @return string
     */
    public function renderRoute(Request $request);

    /**
     * Returns callback name/identifier for the current route based on the callback.
     * Useful if you need to get a unique identifier for the loaded route, for instance
     * when using translations etc.
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Set allowed request methods
     *
     * @param array $methods
     * @return static $this
     */
    public function setRequestMethods(array $methods);

    /**
     * Get allowed request methods
     *
     * @return array
     */
    public function getRequestMethods();

    /**
     * @return IRoute|null
     */
    public function getParent();

    /**
     * Get the group for the route.
     *
     * @return IGroupRoute|null
     */
    public function getGroup();

    /**
     * Set group
     *
     * @param IGroupRoute $group
     * @return static $this
     */
    public function setGroup(IGroupRoute $group);

    /**
     * Set parent route
     *
     * @param IRoute $parent
     * @return static $this
     */
    public function setParent(IRoute $parent);

    /**
     * Set callback
     *
     * @param string $callback
     * @return static
     */
    public function setCallback($callback);

    /**
     * @return string
     */
    public function getCallback();

    public function getMethod();

    public function getClass();

    public function setMethod($method);

    /**
     * @param string $namespace
     * @return static $this
     */
    public function setNamespace($namespace);

    /**
     * @return string
     */
    public function getNamespace();

    /**
     * @param string $namespace
     * @return static $this
     */
    public function setDefaultNamespace($namespace);

    public function getDefaultNamespace();

    /**
     * Get parameter names.
     *
     * @return array
     */
    public function getWhere();

    /**
     * Set parameter names.
     *
     * @param array $options
     * @return static
     */
    public function setWhere(array $options);

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParameters();

    /**
     * Get parameters
     *
     * @param array $parameters
     * @return static $this
     */
    public function setParameters(array $parameters);

    /**
     * Merge with information from another route.
     *
     * @param array $settings
     * @param bool $merge
     * @return static $this
     */
    public function setSettings(array $settings, $merge = false);

    /**
     * Export route settings to array so they can be merged with another route.
     *
     * @return array
     */
    public function toArray();

    /**
     * Get middlewares array
     *
     * @return array
     */
    public function getMiddlewares();

    /**
     * Set middleware class-name
     *
     * @param string $middleware
     * @return static
     */
    public function addMiddleware($middleware);

    /**
     * Set middlewares array
     *
     * @param array $middlewares
     * @return $this
     */
    public function setMiddlewares(array $middlewares);

}