This document talks about the implementation of the code for the Api.

Everything comes under namespace Razorpay\Api\.
Namespaces put a requirement of PHP 5.3 minimum

```php
namespace Razorpay\

class Api
{

    // Contains a __get function that returns the appropriate
    // class object when one tries to access them.
}



namespace Razorpay\

class Client
{
    // Handles request and response
    // Uses Composer:Requests internally
}

class Payment
{
    public function get($id)
    {

    }

    public function fetch($options)
    {

    }

    public function capture($id)
    {

    }

    public function refund($id)
    {

    }
}
```