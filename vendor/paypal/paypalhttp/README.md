## PayPal HttpClient

PayPalHttp is a generic HTTP Client.

In it's simplest form, an [`HttpClient`](lib/PayPalHttp/HttpClient.php) exposes an `execute` method which takes an [HTTP request](lib/PayPalHttp/HttpRequest.php), executes it against the domain described in an [Environment](lib/PayPalHttp/Environment.php), and returns an [HTTP response](lib/PayPalHttp/HttpResponse.php).

### Environment

An [`Environment`](./lib/PayPalHttp/environment.rb) describes a domain that hosts a REST API, against which an `HttpClient` will make requests. `Environment` is a simple interface that wraps one method, `baseUrl`.

```php
$env = new Environment('https://example.com');
```

### Requests

HTTP requests contain all the information needed to make an HTTP request against the REST API. Specifically, one request describes a path, a verb, any path/query/form parameters, headers, attached files for upload, and body data.

### Responses

HTTP responses contain information returned by a server in response to a request as described above. They are simple objects which contain a status code, headers, and any data returned by the server.

```php
$request = new HttpRequest("/path", "GET");
$request->body[] = "some data";

$response = $client->execute($req);

$statusCode = $response->statusCode;
$headers = $response->headers;
$data = $response->result;
```

### Injectors

Injectors are blocks that can be used for executing arbitrary pre-flight logic, such as modifying a request or logging data. Injectors are attached to an `HttpClient` using the `addInjector` method.

The `HttpClient` executes its injectors in a first-in, first-out order, before each request.

```php
class LogInjector implements Injector
{
    public function inject($httpRequest)
    {
        // Do some logging here
    }
}

$logInjector = new LogInjector();
$client = new HttpClient($environment);
$client->addInjector($logInjector);
...
```

### Error Handling

`HttpClient#execute` may throw an `Exception` if something went wrong during the course of execution. If the server returned a non-200 response, [IOException](lib/PayPalHttp/IOException.php) will be thrown, that will contain a status code and headers you can use for debugging.

```php
try
{
    $client->execute($req);
}
catch (HttpException $e)
{
    $statusCode = $e->response->statusCode;
    $headers = $e->response->headers;
    $body = $e->response->result;
}
```

## License
PayPalHttp-PHP is open source and available under the MIT license. See the [LICENSE](./LICENSE) file for more information.

## Contributing
Pull requests and issues are welcome. Please see [CONTRIBUTING.md](./CONTRIBUTING.md) for more details.
