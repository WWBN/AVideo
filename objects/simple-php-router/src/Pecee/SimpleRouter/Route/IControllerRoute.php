<?php
namespace Pecee\SimpleRouter\Route;

interface IControllerRoute extends IRoute
{
    /**
     * Get controller class-name
     *
     * @return string
     */
    public function getController();

    /**
     * Set controller class-name
     *
     * @param string $controller
     * @return static
     */
    public function setController($controller);

    /**
     * Return active method
     *
     * @return string
     */
    public function getMethod();

    /**
     * Set active method
     *
     * @param string $method
     * @return static
     */
    public function setMethod($method);

}