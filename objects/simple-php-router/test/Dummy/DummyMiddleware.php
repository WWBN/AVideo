<?php
require_once 'Exceptions/MiddlewareLoadedException.php';

use Pecee\Http\Request;

class DummyMiddleware implements \Pecee\Http\Middleware\IMiddleware
{
	public function handle(Request $request)
	{
		throw new MiddlewareLoadedException('Middleware loaded!');
	}

}