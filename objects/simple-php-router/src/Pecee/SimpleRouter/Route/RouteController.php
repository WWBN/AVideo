<?php

namespace Pecee\SimpleRouter\Route;

use Pecee\Http\Request;

class RouteController extends LoadableRoute implements IControllerRoute
{
    protected $defaultMethod = 'index';
    protected $controller;
    protected $method;
    protected $names = [];

    public function __construct($url, $controller)
    {
        $this->setUrl($url);
        $this->setName(trim(str_replace('/', '.', $url), '/'));
        $this->controller = $controller;
    }

    /**
     * Check if route has given name.
     *
     * @param string $name
     * @return bool
     */
    public function hasName($name)
    {
        if ($this->name === null) {
            return false;
        }

        /* Remove method/type */
        if (strpos($name, '.') !== false) {
            $method = substr($name, strrpos($name, '.') + 1);
            $newName = substr($name, 0, strrpos($name, '.'));

            if (in_array($method, $this->names, false) === true && strtolower($this->name) === strtolower($newName)) {
                return true;
            }
        }

        return parent::hasName($name);
    }

    /**
     * @param string|null $method
     * @param string|array|null $parameters
     * @param string|null $name
     * @return string
     */
    public function findUrl($method = null, $parameters = null, $name = null)
    {
        if (strpos($name, '.') !== false) {
            $found = array_search(substr($name, strrpos($name, '.') + 1), $this->names, false);
            if ($found !== false) {
                $method = (string)$found;
            }
        }

        $url = '';
        $parameters = (array)$parameters;

        if ($method !== null) {

            /* Remove requestType from method-name, if it exists */
            foreach (static::$requestTypes as $requestType) {

                if (stripos($method, $requestType) === 0) {
                    $method = (string)substr($method, strlen($requestType));
                    break;
                }
            }

            $method .= '/';
        }

        $group = $this->getGroup();

        if ($group !== null && count($group->getDomains()) !== 0) {
            $url .= '//' . $group->getDomains()[0];
        }

        $url .= '/' . trim($this->getUrl(), '/') . '/' . strtolower($method) . implode('/', $parameters);

        return '/' . trim($url, '/') . '/';
    }

    public function matchRoute($url, Request $request)
    {
        if($this->getGroup() !== null && $this->getGroup()->matchRoute($url, $request) === false) {
            return false;
        }

        /* Match global regular-expression for route */
        $regexMatch = $this->matchRegex($request, $url);

        if ($regexMatch === false || (stripos($url, $this->url) !== 0 && strtolower($url) !== strtolower($this->url))) {
            return false;
        }

        $strippedUrl = trim(str_ireplace($this->url, '/', $url), '/');
        $path = explode('/', $strippedUrl);

        if (count($path) !== 0) {

            $method = (isset($path[0]) === false || trim($path[0]) === '') ? $this->defaultMethod : $path[0];
            $this->method = $request->getMethod() . ucfirst($method);

            $this->parameters = array_slice($path, 1);

            // Set callback
            $this->setCallback($this->controller . '@' . $this->method);

            return true;
        }

        return false;
    }

    /**
     * Get controller class-name.
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get controller class-name.
     *
     * @param string $controller
     * @return static
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Return active method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set active method
     *
     * @param string $method
     * @return static
     */
    public function setMethod($method)
    {
        $this->method = $method;

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
        if (isset($values['names']) === true) {
            $this->names = $values['names'];
        }

        parent::setSettings($values, $merge);

        return $this;
    }

}