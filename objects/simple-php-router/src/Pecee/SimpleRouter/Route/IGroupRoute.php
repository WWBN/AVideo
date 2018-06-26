<?php

namespace Pecee\SimpleRouter\Route;

use Pecee\Handlers\IExceptionHandler;
use Pecee\Http\Request;

interface IGroupRoute extends IRoute
{
    /**
     * Method called to check if a domain matches
     *
     * @param Request $request
     * @return bool
     */
    public function matchDomain(Request $request);

    /**
     * Add exception handler
     *
     * @param IExceptionHandler|string $handler
     * @return static $this;
     */
    public function addExceptionHandler($handler);

    /**
     * Set exception-handlers for group
     *
     * @param array $handlers
     * @return static $this
     */
    public function setExceptionHandlers(array $handlers);

    /**
     * Get exception-handlers for group
     *
     * @return array
     */
    public function getExceptionHandlers();

    /**
     * Get domains for domain.
     *
     * @return array
     */
    public function getDomains();

    /**
     * Set allowed domains for group.
     *
     * @param array $domains
     * @return $this
     */
    public function setDomains(array $domains);

    /**
     * Set prefix that child-routes will inherit.
     *
     * @param string $prefix
     * @return string
     */
    public function setPrefix($prefix);

    /**
     * Get prefix.
     *
     * @return string
     */
    public function getPrefix();
}