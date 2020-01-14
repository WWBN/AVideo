<?php
namespace Pecee\Http\Middleware;

use Pecee\Http\Request;

interface IMiddleware
{
    /**
     * @param Request $request
     * @return Request|null
     */
    public function handle(Request $request);

}