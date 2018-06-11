<?php

namespace Pecee\SimpleRouter\Route;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
use Pecee\SimpleRouter\Exceptions\NotFoundHttpException;

abstract class Route implements IRoute
{
    const PARAMETERS_REGEX_FORMAT = '%s([\w]+)(\%s?)%s';
    const PARAMETERS_DEFAULT_REGEX = '[\w]+';

    const REQUEST_TYPE_GET = 'get';
    const REQUEST_TYPE_POST = 'post';
    const REQUEST_TYPE_PUT = 'put';
    const REQUEST_TYPE_PATCH = 'patch';
    const REQUEST_TYPE_OPTIONS = 'options';
    const REQUEST_TYPE_DELETE = 'delete';

    public static $requestTypes = [
        self::REQUEST_TYPE_GET,
        self::REQUEST_TYPE_POST,
        self::REQUEST_TYPE_PUT,
        self::REQUEST_TYPE_PATCH,
        self::REQUEST_TYPE_OPTIONS,
        self::REQUEST_TYPE_DELETE,
    ];

    /**
     * If enabled parameters containing null-value
     * will not be passed along to the callback.
     *
     * @var bool
     */
    protected $filterEmptyParams = true;

    /**
     * Default regular expression used for parsing parameters.
     * @var string|null
     */
    protected $defaultParameterRegex;
    protected $paramModifiers = '{}';
    protected $paramOptionalSymbol = '?';
    protected $urlRegex = '/^%s\/?$/u';
    protected $group;
    protected $parent;
    protected $callback;
    protected $defaultNamespace;

    /* Default options */
    protected $namespace;
    protected $requestMethods = [];
    protected $where = [];
    protected $parameters = [];
    protected $originalParameters = [];
    protected $middlewares = [];

    /**
     * Load class by name
     * @param string $name
     * @return mixed
     * @throws NotFoundHttpException
     */
    protected function loadClass($name)
    {
        if (class_exists($name) === false) {
            throw new NotFoundHttpException(sprintf('Class "%s" does not exist', $name), 404);
        }

        return new $name();
    }

    /**
     * Render route
     *
     * @param Request $request
     * @return string|mixed
     * @throws NotFoundHttpException
     */
    public function renderRoute(Request $request)
    {
        $callback = $this->getCallback();

        if ($callback === null) {
            return null;
        }

        $parameters = $this->getParameters();

        /* Filter parameters with null-value */

        if ($this->filterEmptyParams === true) {
            $parameters = array_filter($parameters, function ($var) {
                return ($var !== null);
            });
        }

        /* Render callback function */
        if (is_callable($callback) === true) {
            /* When the callback is a function */
            return call_user_func_array($callback, $parameters);
        }

        /* When the callback is a class + method */
        $controller = explode('@', $callback);

        $namespace = $this->getNamespace();

        $className = ($namespace !== null && $controller[0][0] !== '\\') ? $namespace . '\\' . $controller[0] : $controller[0];

        $class = $this->loadClass($className);
        $method = $controller[1];

        if (method_exists($class, $method) === false) {
            throw new NotFoundHttpException(sprintf('Method "%s" does not exist in class "%s"', $method, $className), 404);
        }

        return call_user_func_array([$class, $method], $parameters);
    }

    protected function parseParameters($route, $url, $parameterRegex = null)
    {
        $regex = sprintf(static::PARAMETERS_REGEX_FORMAT, $this->paramModifiers[0], $this->paramOptionalSymbol, $this->paramModifiers[1]);

        $parameters = [];

        // Ensures that hostnames/domains will work with parameters
        $url = '/' . ltrim($url, '/');

        if ((bool)preg_match_all('/' . $regex . '/u', $route, $parameters) === false) {
            $urlRegex = preg_quote($route, '/');
        } else {

            $urlParts = preg_split('/((\-?\/?)\{[^}]+\})/', $route);

            foreach ($urlParts as $key => $t) {

                $regex = '';

                if ($key < count($parameters[1])) {

                    $name = $parameters[1][$key];

                    /* If custom regex is defined, use that */
                    if (isset($this->where[$name]) === true) {
                        $regex = $this->where[$name];
                    } else {

                        /* If method specific regex is defined use that, otherwise use the default parameter regex */
                        if ($parameterRegex !== null) {
                            $regex = $parameterRegex;
                        } else {
                            $regex = ($this->defaultParameterRegex === null) ? static::PARAMETERS_DEFAULT_REGEX : $this->defaultParameterRegex;
                        }
                    }

                    $regex = sprintf('(?:\/|\-)%1$s(?P<%2$s>%3$s)%1$s', $parameters[2][$key], $name, $regex);
                }

                $urlParts[$key] = preg_quote($t, '/') . $regex;
            }

            $urlRegex = implode('', $urlParts);

        }

        if ((bool)preg_match(sprintf($this->urlRegex, $urlRegex), $url, $matches) === false) {
            return null;
        }

        $values = [];

        if (isset($parameters[1]) === true) {

            /* Only take matched parameters with name */
            foreach ((array)$parameters[1] as $name) {
                $values[$name] = (isset($matches[$name]) && $matches[$name] !== '') ? $matches[$name] : null;
            }
        }

        return $values;
    }

    /**
     * Returns callback name/identifier for the current route based on the callback.
     * Useful if you need to get a unique identifier for the loaded route, for instance
     * when using translations etc.
     *
     * @return string
     */
    public function getIdentifier()
    {
        if (is_string($this->callback) === true && strpos($this->callback, '@') !== false) {
            return $this->callback;
        }

        return 'function_' . md5($this->callback);
    }

    /**
     * Set allowed request methods
     *
     * @param array $methods
     * @return static $this
     */
    public function setRequestMethods(array $methods)
    {
        $this->requestMethods = $methods;

        return $this;
    }

    /**
     * Get allowed request methods
     *
     * @return array
     */
    public function getRequestMethods()
    {
        return $this->requestMethods;
    }

    /**
     * @return IRoute|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get the group for the route.
     *
     * @return IGroupRoute|null
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set group
     *
     * @param IGroupRoute $group
     * @return static $this
     */
    public function setGroup(IGroupRoute $group)
    {
        $this->group = $group;

        /* Add/merge parent settings with child */
        $this->setSettings($group->toArray(), true);

        return $this;
    }

    /**
     * Set parent route
     *
     * @param IRoute $parent
     * @return static $this
     */
    public function setParent(IRoute $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Set callback
     *
     * @param string $callback
     * @return static
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * @return string
     */
    public function getCallback()
    {
        return $this->callback;
    }

    public function getMethod()
    {
        if (is_string($this->callback) === true && strpos($this->callback, '@') !== false) {
            $tmp = explode('@', $this->callback);

            return $tmp[1];
        }

        return null;
    }

    public function getClass()
    {
        if (is_string($this->callback) === true && strpos($this->callback, '@') !== false) {
            $tmp = explode('@', $this->callback);

            return $tmp[0];
        }

        return null;
    }

    public function setMethod($method)
    {
        $this->callback = sprintf('%s@%s', $this->getClass(), $method);

        return $this;
    }

    public function setClass($class)
    {
        $this->callback = sprintf('%s@%s', $class, $this->getMethod());

        return $this;
    }

    /**
     * @param string $namespace
     * @return static $this
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @param string $namespace
     * @return static $this
     */
    public function setDefaultNamespace($namespace)
    {
        $this->defaultNamespace = $namespace;

        return $this;
    }

    public function getDefaultNamespace()
    {
        return $this->defaultNamespace;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return ($this->namespace === null) ? $this->defaultNamespace : $this->namespace;
    }

    /**
     * Export route settings to array so they can be merged with another route.
     *
     * @return array
     */
    public function toArray()
    {
        $values = [];

        if ($this->namespace !== null) {
            $values['namespace'] = $this->namespace;
        }

        if (count($this->requestMethods) !== 0) {
            $values['method'] = $this->requestMethods;
        }

        if (count($this->where) !== 0) {
            $values['where'] = $this->where;
        }

        if (count($this->middlewares) !== 0) {
            $values['middleware'] = $this->middlewares;
        }

        if ($this->defaultParameterRegex !== null) {
            $values['defaultParameterRegex'] = $this->defaultParameterRegex;
        }

        return $values;
    }

    /**
     * Merge with information from another route.
     *
     * @param array $values
     * @param bool $merge
     * @return static $this
     */
    public function setSettings(array $values, $merge = false)
    {
        if ($this->namespace === null && isset($values['namespace']) === true) {
            $this->setNamespace($values['namespace']);
        }

        if (isset($values['method']) === true) {
            $this->setRequestMethods(array_merge($this->requestMethods, (array)$values['method']));
        }

        if (isset($values['where']) === true) {
            $this->setWhere(array_merge($this->where, (array)$values['where']));
        }

        if (isset($values['parameters']) === true) {
            $this->setParameters(array_merge($this->parameters, (array)$values['parameters']));
        }

        // Push middleware if multiple
        if (isset($values['middleware']) === true) {
            $this->setMiddlewares(array_merge((array)$values['middleware'], $this->middlewares));
        }

        if (isset($values['defaultParameterRegex']) === true) {
            $this->setDefaultParameterRegex($values['defaultParameterRegex']);
        }

        return $this;
    }

    /**
     * Get parameter names.
     *
     * @return array
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * Set parameter names.
     *
     * @param array $options
     * @return static
     */
    public function setWhere(array $options)
    {
        $this->where = $options;

        return $this;
    }

    /**
     * Add regular expression parameter match.
     * Alias for LoadableRoute::where()
     *
     * @see LoadableRoute::where()
     * @param array $options
     * @return static
     */
    public function where(array $options)
    {
        return $this->setWhere($options);
    }

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParameters()
    {
        /* Sort the parameters after the user-defined param order, if any */
        $parameters = [];

        if (count($this->originalParameters) !== 0) {
            $parameters = $this->originalParameters;
        }

        return array_merge($parameters, $this->parameters);
    }

    /**
     * Get parameters
     *
     * @param array $parameters
     * @return static $this
     */
    public function setParameters(array $parameters)
    {
        /*
         * If this is the first time setting parameters we store them so we
         * later can organize the array, in case somebody tried to sort the array.
         */
        if (count($parameters) !== 0 && count($this->originalParameters) === 0) {
            $this->originalParameters = $parameters;
        }

        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    /**
     * Add middleware class-name
     *
     * @deprecated This method is deprecated and will be removed in the near future.
     * @param IMiddleware|string $middleware
     * @return static
     */
    public function setMiddleware($middleware)
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * Add middleware class-name
     *
     * @param IMiddleware|string $middleware
     * @return static
     */
    public function addMiddleware($middleware)
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * Set middlewares array
     *
     * @param array $middlewares
     * @return $this
     */
    public function setMiddlewares(array $middlewares)
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * @return array
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * Set default regular expression used when matching parameters.
     * This is used when no custom parameter regex is found.
     *
     * @param string $regex
     * @return static $this
     */
    public function setDefaultParameterRegex($regex)
    {
        $this->defaultParameterRegex = $regex;

        return $this;
    }

    /**
     * Get default regular expression used when matching parameters.
     *
     * @return string
     */
    public function getDefaultParameterRegex()
    {
        return $this->defaultParameterRegex;
    }

}