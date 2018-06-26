<?php
namespace Pecee\SimpleRouter;

use Pecee\Http\Request;

interface IRouterBootManager
{
    /**
     * Called when router loads it's routes
     *
     * @param Request $request
     * @return Request
     */
    public function boot(Request $request);
}