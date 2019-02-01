<?php

class ExceptionHandler implements \Pecee\Handlers\IExceptionHandler
{
	public function handleError(\Pecee\Http\Request $request, \Exception $error)
	{
	    echo $error->getMessage();
	}

}