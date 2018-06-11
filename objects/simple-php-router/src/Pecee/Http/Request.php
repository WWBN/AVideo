<?php

namespace Pecee\Http;

use Pecee\Http\Input\Input;
use Pecee\SimpleRouter\Route\ILoadableRoute;
use Pecee\SimpleRouter\Route\RouteUrl;
use Pecee\SimpleRouter\SimpleRouter;

class Request
{
    private $data = [];
    protected $headers;
    protected $host;
    protected $url;
    protected $method;
    protected $input;

    protected $hasRewrite = false;

    /**
     * @var ILoadableRoute|null
     */
    protected $rewriteRoute;
    protected $rewriteUrl;

    /**
     * @var ILoadableRoute|null
     */
    protected $loadedRoute;

    /**
     * Request constructor.
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public function __construct()
    {
        $this->parseHeaders();
        $this->setHost($this->getHeader('http-host'));

        // Check if special IIS header exist, otherwise use default.
        $this->setUrl($this->getHeader('unencoded-url', $this->getHeader('request-uri')));

        $this->input = new Input($this);
        $this->method = strtolower($this->input->get('_method', $this->getHeader('request-method')));
    }

    protected function parseHeaders()
    {
        $this->headers = [];

        foreach ($_SERVER as $key => $value) {
            $this->headers[strtolower($key)] = $value;
            $this->headers[strtolower(str_replace('_', '-', $key))] = $value;
        }

    }

    public function isSecure()
    {
        return $this->getHeader('http-x-forwarded-proto') === 'https' || $this->getHeader('https') !== null || $this->getHeader('server-port') === 443;
    }

    /**
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get http basic auth user
     * @return string|null
     */
    public function getUser()
    {
        return $this->getHeader('php-auth-user');
    }

    /**
     * Get http basic auth password
     * @return string|null
     */
    public function getPassword()
    {
        return $this->getHeader('php-auth-pw');
    }

    /**
     * Get all headers
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get id address
     * @return string
     */
    public function getIp()
    {
        if ($this->getHeader('http-cf-connecting-ip') !== null) {
            return $this->getHeader('http-cf-connecting-ip');
        }

        if ($this->getHeader('http-x-forwarded-for') !== null) {
            return $this->getHeader('http-x-forwarded_for');
        }

        return $this->getHeader('remote-addr');
    }

    /**
     * Get remote address/ip
     *
     * @alias static::getIp
     * @return string
     */
    public function getRemoteAddr()
    {
        return $this->getIp();
    }

    /**
     * Get referer
     * @return string
     */
    public function getReferer()
    {
        return $this->getHeader('http-referer');
    }

    /**
     * Get user agent
     * @return string
     */
    public function getUserAgent()
    {
        return $this->getHeader('http-user-agent');
    }

    /**
     * Get header value by name
     *
     * @param string $name
     * @param string|null $defaultValue
     *
     * @return string|null
     */
    public function getHeader($name, $defaultValue = null)
    {
        return isset($this->headers[strtolower($name)]) ? $this->headers[strtolower($name)] : $defaultValue;
    }

    /**
     * Get input class
     * @return Input
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Is format accepted
     *
     * @param string $format
     *
     * @return bool
     */
    public function isFormatAccepted($format)
    {
        return ($this->getHeader('http-accept') !== null && stripos($this->getHeader('http-accept'), $format) > -1);
    }

    /**
     * Returns true if the request is made through Ajax
     *
     * @return bool
     */
    public function isAjax()
    {
        return (strtolower($this->getHeader('http-x-requested-with')) === 'xmlhttprequest');
    }

    /**
     * Get accept formats
     * @return array
     */
    public function getAcceptFormats()
    {
        return explode(',', $this->getHeader('http-accept'));
    }

    /**
     * @param string|Url $url
     * @throws \Pecee\Http\Exceptions\MalformedUrlException
     */
    public function setUrl($url)
    {
        $this->url = ($url instanceof Url) ? $url : new Url($url);
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Set rewrite route
     *
     * @param ILoadableRoute $route
     * @return static
     */
    public function setRewriteRoute(ILoadableRoute $route)
    {
        $this->hasRewrite = true;
        $this->rewriteRoute = SimpleRouter::addDefaultNamespace($route);

        return $this;
    }

    /**
     * Get rewrite route
     *
     * @return ILoadableRoute|null
     */
    public function getRewriteRoute()
    {
        return $this->rewriteRoute;
    }

    /**
     * Get rewrite url
     *
     * @return string
     */
    public function getRewriteUrl()
    {
        return $this->rewriteUrl;
    }

    /**
     * Set rewrite url
     *
     * @param string $rewriteUrl
     * @return static
     */
    public function setRewriteUrl($rewriteUrl)
    {
        $this->hasRewrite = true;
        $this->rewriteUrl = rtrim($rewriteUrl, '/') . '/';

        return $this;
    }

    /**
     * Set rewrite callback
     * @param string $callback
     * @return static
     */
    public function setRewriteCallback($callback)
    {
        $this->hasRewrite = true;

        return $this->setRewriteRoute(new RouteUrl($this->getUrl()->getPath(), $callback));
    }

    /**
     * Get loaded route
     * @return ILoadableRoute|null
     */
    public function getLoadedRoute()
    {
        return $this->loadedRoute;
    }

    /**
     * Set loaded route
     *
     * @param ILoadableRoute $route
     * @return static
     */
    public function setLoadedRoute(ILoadableRoute $route)
    {
        $this->loadedRoute = $route;

        return $this;
    }

    public function hasRewrite()
    {
        return $this->hasRewrite;
    }

    public function setHasRewrite($value)
    {
        $this->hasRewrite = $value;

        return $this;
    }

    public function isRewrite($url)
    {
        return ($this->rewriteUrl === $url);
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->data);
    }

    public function __set($name, $value = null)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

}