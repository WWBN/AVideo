<?php

namespace Pecee\SimpleRouter\Route;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
use Pecee\SimpleRouter\Exceptions\HttpException;

abstract class LoadableRoute extends Route implements ILoadableRoute
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $name;

    protected $regex;

    /**
     * Loads and renders middlewares-classes
     *
     * @param Request $request
     * @throws HttpException
     */
    public function loadMiddleware(Request $request)
    {
        foreach ($this->getMiddlewares() as $middleware) {

            if (is_object($middleware) === false) {
                $middleware = $this->loadClass($middleware);
            }

            if (($middleware instanceof IMiddleware) === false) {
                throw new HttpException($middleware . ' must be inherit the IMiddleware interface');
            }

            $middleware->handle($request);
        }
    }

    public function matchRegex(Request $request, $url)
    {
        /* Match on custom defined regular expression */

        if ($this->regex === null) {
            return null;
        }

        return ((bool)preg_match($this->regex, $request->getHost() . $url) !== false);
    }

    /**
     * Set url
     *
     * @param string $url
     * @return static
     */
    public function setUrl($url)
    {
        $this->url = ($url === '/') ? '/' : '/' . trim($url, '/') . '/';

        if (strpos($this->url, $this->paramModifiers[0]) !== false) {

            $regex = sprintf(static::PARAMETERS_REGEX_FORMAT, $this->paramModifiers[0], $this->paramOptionalSymbol, $this->paramModifiers[1]);

            if ((bool)preg_match_all('/' . $regex . '/u', $this->url, $matches) !== false) {
                $this->parameters = array_fill_keys($matches[1], null);
            }
        }

        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Find url that matches method, parameters or name.
     * Used when calling the url() helper.
     *
     * @param string|null $method
     * @param string|array|null $parameters
     * @param string|null $name
     * @return string
     */
    public function findUrl($method = null, $parameters = null, $name = null)
    {
        $url = $this->getUrl();

        $group = $this->getGroup();

        if ($group !== null && count($group->getDomains()) !== 0) {
            $url = '//' . $group->getDomains()[0] . $url;
        }

        /* Contains parameters that aren't recognized and will be appended at the end of the url */
        $unknownParams = [];

        /* Create the param string - {parameter} */
        $param1 = $this->paramModifiers[0] . '%s' . $this->paramModifiers[1];

        /* Create the param string with the optional symbol - {parameter?} */
        $param2 = $this->paramModifiers[0] . '%s' . $this->paramOptionalSymbol . $this->paramModifiers[1];

        /* Replace any {parameter} in the url with the correct value */

        $params = $this->getParameters();

        foreach (array_keys($params) as $param) {

            if ($parameters === '' || (is_array($parameters) === true && count($parameters) === 0)) {
                $value = '';
            } else {
                $p = (array)$parameters;
                $value = array_key_exists($param, $p) ? $p[$param] : $params[$param];

                /* If parameter is specifically set to null - use the original-defined value */
                if ($value === null && isset($this->originalParameters[$param])) {
                    $value = $this->originalParameters[$param];
                }
            }

            if (stripos($url, $param1) !== false || stripos($url, $param) !== false) {
                /* Add parameter to the correct position */
                $url = str_ireplace([sprintf($param1, $param), sprintf($param2, $param)], $value, $url);
            } else {
                $unknownParams[$param] = $value;
            }
        }

        $url = '/' . ltrim($url, '/') . implode('/', $unknownParams);

        return rtrim($url, '/') . '/';
    }

    /**
     * Returns the provided name for the router.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Check if route has given name.
     *
     * @param string $name
     * @return bool
     */
    public function hasName($name)
    {
        return (strtolower($this->name) === strtolower($name));
    }

    /**
     * Add regular expression match for the entire route.
     *
     * @param string $regex
     * @return static
     */
    public function setMatch($regex)
    {
        $this->regex = $regex;

        return $this;
    }

    /**
     * Get regular expression match used for matching route (if defined).
     *
     * @return string
     */
    public function getMatch()
    {
        return $this->regex;
    }

    /**
     * Sets the router name, which makes it easier to obtain the url or router at a later point.
     * Alias for LoadableRoute::setName().
     *
     * @see LoadableRoute::setName()
     * @param string|array $name
     * @return static
     */
    public function name($name)
    {
        return $this->setName($name);
    }

    /**
     * Sets the router name, which makes it easier to obtain the url or router at a later point.
     *
     * @param string $name
     * @return static $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Merge with information from another route.
     *
     * @param array $values
     * @param bool $merge
     * @return static
     */
    public function setSettings(array $values, $merge = false)
    {
        if (isset($values['as']) === true) {

            $name = $values['as'];

            if ($this->name !== null && $merge !== false) {
                $name .= '.' . $this->name;
            }

            $this->setName($name);
        }

        if (isset($values['prefix']) === true) {
            $this->setUrl($values['prefix'] . $this->getUrl());
        }

        parent::setSettings($values, $merge);

        return $this;
    }

}