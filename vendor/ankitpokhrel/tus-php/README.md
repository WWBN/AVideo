<h1 align="center">TusPHP</h1>

<p align="center">
    <a href="https://packagist.org/packages/ankitpokhrel/tus-php">
        <img alt="PHP Version" src="https://img.shields.io/badge/php-7.2.5%2B-brightgreen.svg?style=flat-square" />
    </a>
    <a href="https://github.com/ankitpokhrel/tus-php/actions/workflows/ci.yml?query=branch%3Amain+is%3Acompleted">
        <img alt="Build Status" src="https://img.shields.io/github/workflow/status/ankitpokhrel/tus-php/CI?style=flat-square" />
    </a>
    <a href="https://scrutinizer-ci.com/g/ankitpokhrel/tus-php">
        <img alt="Code Coverage" src="https://img.shields.io/scrutinizer/coverage/g/ankitpokhrel/tus-php.svg?style=flat-square" />
    </a>
    <a href="https://scrutinizer-ci.com/g/ankitpokhrel/tus-php">
        <img alt="Scrutinizer Code Quality" src="https://img.shields.io/scrutinizer/g/ankitpokhrel/tus-php.svg?style=flat-square" />
    </a>
    <a href="https://packagist.org/packages/ankitpokhrel/tus-php">
        <img alt="Downloads" src="https://img.shields.io/packagist/dm/ankitpokhrel/tus-php.svg?style=flat-square" />
    </a>
    <a href="https://github.com/ankitpokhrel/tus-php/blob/main/LICENSE">
        <img alt="Software License" src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" />
    </a>
</p>

<p align="center">
    <i align="center">Resumable file upload in PHP using <a href="https://tus.io">tus resumable upload protocol v1.0.0</a></i>
</p>

<p align="center">
    <img alt="TusPHP Demo" src="https://github.com/ankitpokhrel/tus-php/blob/main/example/demo.gif" /><br/><br/>
    <a href="https://medium.com/@ankitpokhrel/resumable-file-upload-in-php-handle-large-file-uploads-in-an-elegant-way-e6c6dfdeaedb">Medium Article</a>&nbsp;⚡&nbsp;<a href="https://github.com/ankitpokhrel/tus-php/wiki/Laravel-&-Lumen-Integration">Laravel & Lumen Integration</a>&nbsp;⚡&nbsp;<a href="https://github.com/ankitpokhrel/tus-php/wiki/Symfony-Integration">Symfony Integration</a>&nbsp;⚡&nbsp;<a href="https://github.com/ankitpokhrel/tus-php/wiki/CakePHP-Integration">CakePHP Integration</a>&nbsp;⚡&nbsp;<a href="https://github.com/ankitpokhrel/tus-php/wiki/WordPress-Integration">WordPress Integration</a>
</p>

<p align="center">
    <a href="https://opencollective.com/tus-php#backers" target="_blank" align="center"><img src="https://opencollective.com/tus-php/backers.svg"></a>
</p>

**tus** is a HTTP based protocol for resumable file uploads. Resumable means you can carry on where you left off without
re-uploading whole data again in case of any interruptions. An interruption may happen willingly if the user wants
to pause, or by accident in case of a network issue or server outage.

### Table of Contents

* [Installation](#installation)
* [Usage](#usage)
    * [Server](#server)
        * [Nginx](#nginx)
        * [Apache](#apache)
    * [Client](#client)
    * [Third Party Client Libraries](#third-party-client-libraries)
    * [Cloud Providers](#cloud-providers)
* [Extension support](#extension-support)
    * [Expiration](#expiration)
    * [Concatenation](#concatenation)
* [Events](#events)
    * [Responding to an Event](#responding-to-an-event)
* [Middleware](#middleware)
    * [Creating a Middleware](#creating-a-middleware)
    * [Adding a Middleware](#adding-a-middleware)
    * [Skipping a Middleware](#skipping-a-middleware)
* [Setting up a dev environment and/or running examples locally](#setting-up-a-dev-environment-andor-running-examples-locally)
    * [Docker](#docker)
* [Contributing](#contributing)
* [Questions about this project?](#questions-about-this-project)
* [Supporters](#supporters)

### Installation

Pull the package via composer.
```shell
$ composer require ankitpokhrel/tus-php

// Use v1 for php7.1, Symfony 3 or 4.

$ composer require ankitpokhrel/tus-php:^1.2
```

### Usage
| ![Basic Tus Architecture](https://cdn-images-1.medium.com/max/2000/1*N4JhqeXJgWA1Z7pc6_5T_A.png "Basic Tus Architecture") |
|:--:|
| Basic Tus Architecture |

#### Server
This is how a simple server looks like.

```php
// server.php

// Either redis, file or apcu. Leave empty for file based cache.
$server   = new \TusPhp\Tus\Server('redis');
$response = $server->serve();

$response->send();

exit(0); // Exit from current PHP process.
```

> :bangbang: File based cache is not recommended for production use.

You need to rewrite your server to respond to a specific endpoint. For example:

###### Nginx
```nginx
# nginx.conf

location /files {
    try_files $uri $uri/ /server.php?$query_string;
}
```

A new config option [fastcgi_request_buffering](http://nginx.org/en/docs/http/ngx_http_fastcgi_module.html#fastcgi_request_buffering) is available since nginx 1.7.11.
When buffering is enabled, the entire request body is read from the client before sending the request to a FastCGI server. Disabling this option might help with timeouts during the upload.
Furthermore, it helps if you’re running out of disc space on the tmp partition of your system.

If you do not turn off `fastcgi_request_buffering` and you use `fastcgi`, you will not be able to resume uploads because nginx will not give the request back to PHP until the entire file is uploaded.

```nginx
location ~ \.php$ {
    # ...

    fastcgi_request_buffering off; # Disable request buffering
    
    # ...
}
```

A sample nginx configuration can be found [here](docker/server/configs/default.conf).

###### Apache
```apache
# .htaccess

RewriteEngine on

RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^files/?(.*)?$ /server.php?$1 [QSA,L]
```

Default max upload size is 0 which means there is no restriction. You can set max upload size as described below.
```php
$server->setMaxUploadSize(100000000); // 100 MB in bytes
```

Default redis and file configuration for server and client can be found inside `config/server.php` and `config/client.php` respectively.
To override default config you can simply copy the file to your preferred location and update the parameters. You then need to set the config before doing anything else.

```php
\TusPhp\Config::set('<path to your config>');

$server = new \TusPhp\Tus\Server('redis');
```

Alternately, you can set `REDIS_HOST`, `REDIS_PORT` and `REDIS_DB` env in your server to override redis settings for both server and client.

#### Client
The client can be used for creating, resuming and/or deleting uploads.

```php
$client = new \TusPhp\Tus\Client($baseUrl);

// Key is mandatory.
$key = 'your unique key';

$client->setKey($key)->file('/path/to/file', 'filename.ext');

// Create and upload a chunk of 1MB
$bytesUploaded = $client->upload(1000000);

// Resume, $bytesUploaded = 2MB
$bytesUploaded = $client->upload(1000000);

// To upload whole file, skip length param
$client->file('/path/to/file', 'filename.ext')->upload();
```

To check if the file was partially uploaded before, you can use `getOffset` method. It returns false if the upload
isn't there or invalid, returns total bytes uploaded otherwise.

```php
$offset = $client->getOffset(); // 2000000 bytes or 2MB
```

Delete partial upload from the cache.

```php
$client->delete($key);
```

By default, the client uses `/files` as an API path. You can change it with `setApiPath` method.

```php
$client->setApiPath('/api');
```

By default, the server will use `sha256` algorithm to verify the integrity of the upload. If you want to use a different hash algorithm, you can do so by
using `setChecksumAlgorithm` method. To get the list of supported hash algorithms, you can send `OPTIONS` request to the server.

```php
$client->setChecksumAlgorithm('crc32');
```

#### Third Party Client Libraries
##### [Uppy](https://uppy.io/)
Uppy is a sleek, modular file uploader plugin developed by same folks behind tus protocol.
You can use uppy to seamlessly integrate official [tus-js-client](https://github.com/tus/tus-js-client) with tus-php server.
Check out more details in [uppy docs](https://uppy.io/docs/tus/).
```js
uppy.use(Tus, {
  endpoint: 'https://tus-server.yoursite.com/files/', // use your tus endpoint here
  resume: true,
  autoRetry: true,
  retryDelays: [0, 1000, 3000, 5000]
})
```

##### [Tus-JS-Client](https://github.com/tus/tus-js-client)
Tus-php server is compatible with the official [tus-js-client](https://github.com/tus/tus-js-client) Javascript library.
```js
var upload = new tus.Upload(file, {
  endpoint: "/tus",
  retryDelays: [0, 3000, 5000, 10000, 20000],
  metadata: {
    name: file.name,
    type: file.type
  }
})
upload.start()
```

#### Cloud Providers
Many cloud providers implement PHP [streamWrapper](https://www.php.net/manual/en/class.streamwrapper.php) interface that enables us to store and retrieve data from these providers using built-in PHP functions. Since tus-php relies on PHP's built-in filesystem functions, we can easily use it to upload files to the providers like [Amazon S3](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/s3-stream-wrapper.html) if their API supports writing in append binary mode. An example implementation to upload files directly to S3 bucket is as follows:

```php
// server.php
// composer require aws/aws-sdk-php

use Aws\S3\S3Client;
use TusPhp\Tus\Server;
use Aws\Credentials\Credentials;

$awsAccessKey = 'AWS_ACCESS_KEY'; // YOUR AWS ACCESS KEY
$awsSecretKey = 'AWS_SECRET_KEY'; // YOUR AWS SECRET KEY
$awsRegion    = 'eu-west-1';      // YOUR AWS BUCKET REGION
$basePath     = 's3://your-bucket-name';

$s3Client = new S3Client([
    'version' => 'latest',
    'region' => $awsRegion,
    'credentials' => new Credentials($awsAccessKey, $awsSecretKey)
]);
$s3Client->registerStreamWrapper();

$server = new Server('file');
$server->setUploadDir($basePath);

$response = $server->serve();
$response->send();

exit(0);
```

### Extension Support
- [x] The Creation extension is mostly implemented and is used for creating the upload. Deferring the upload's length is not possible at the moment.
- [x] The Termination extension is implemented which is used to terminate completed and unfinished uploads allowing the Server to free up used resources.
- [x] The Checksum extension is implemented, the server will use `sha256` algorithm by default to verify the upload.
- [x] The Expiration extension is implemented, details below.
- [x] This Concatenation extension is implemented except that the server is not capable of handling unfinished concatenation.

#### Expiration
The Server is capable of removing expired but unfinished uploads. You can use the following command manually or in a
cron job to remove them. Note that this command checks your cache storage to find expired uploads. So, make sure
to run it before the cache is expired, else it will not find all files that needs to be cleared.

```shell
$ ./vendor/bin/tus tus:expired --help

Usage:
  tus:expired [<cache-adapter>] [options]

Arguments:
  cache-adapter         Cache adapter to use: redis, file or apcu [default: "file"]

Options:
  -c, --config=CONFIG   File to get config parameters from.

eg:

$ ./vendor/bin/tus tus:expired redis

Cleaning server resources
=========================

1. Deleted 1535888128_35094.jpg from /var/www/uploads
```

You can use`--config` option to override default redis or file configuration.

 ```shell
 $ ./vendor/bin/tus tus:expired redis --config=<path to your config file>
 ```

#### Concatenation
The Server is capable of concatenating multiple uploads into a single one enabling Clients to perform parallel uploads and to upload non-contiguous chunks.

```php
// Actual file key
$uploadKey = uniqid();

$client->setKey($uploadKey)->file('/path/to/file', 'chunk_a.ext');

// Upload 10000 bytes starting from 1000 bytes
$bytesUploaded = $client->seek(1000)->upload(10000);
$chunkAkey     = $client->getKey();

// Upload 1000 bytes starting from 0 bytes
$bytesUploaded = $client->setFileName('chunk_b.ext')->seek(0)->upload(1000);
$chunkBkey     = $client->getKey();

// Upload remaining bytes starting from 11000 bytes (10000 +  1000)
$bytesUploaded = $client->setFileName('chunk_c.ext')->seek(11000)->upload();
$chunkCkey     = $client->getKey();

// Concatenate partial uploads
$client->setFileName('actual_file.ext')->concat($uploadKey, $chunkBkey, $chunkAkey, $chunkCkey);
```

Additionally, the server will verify checksum against the merged file to make sure that the file is not corrupt.

### Events
Often times, you may want to perform some operation after the upload is complete or created. For example, you may want to crop images after upload or transcode a file and email it to your user.
You can utilize tus events for these operations. Following events are dispatched by server during different point of execution.

| Event Name | Dispatched |
-------------|------------|
| `tus-server.upload.created` | after the upload is created during `POST` request. |
| `tus-server.upload.progress` | after a chunk is uploaded during `PATCH` request. |
| `tus-server.upload.complete` | after the upload is complete and checksum verification is done. |
| `tus-server.upload.merged` | after all partial uploads are merged during concatenation request. |

#### Responding to an Event
To listen to an event, you can simply attach a listener to the event name. An `TusEvent` instance is created and passed to all of the listeners.

```php
$server->event()->addListener('tus-server.upload.complete', function (\TusPhp\Events\TusEvent $event) {
    $fileMeta = $event->getFile()->details();
    $request  = $event->getRequest();
    $response = $event->getResponse();

    // ...
});
```

or, you can also bind some method of a custom class.

```php
/**
 * Listener can be method from any normal class.
 */
class SomeClass
{
    public function postUploadOperation(\TusPhp\Events\TusEvent $event)
    {
        // ...
    }
}

$listener = new SomeClass();

$server->event()->addListener('tus-server.upload.complete', [$listener, 'postUploadOperation']);
```

### Middleware
You can manipulate request and response of a server using a middleware. Middleware can be used to run a piece of code before a server calls the actual handle method.
You can use middleware to authenticate a request, handle CORS, whitelist/blacklist an IP etc.

#### Creating a Middleware
In order to create a middleware, you need to implement `TusMiddleware` interface. The handle method provides request and response object for you to manipulate.

```php
<?php

namespace Your\Namespace;

use TusPhp\Request;
use TusPhp\Response;
use TusPhp\Middleware\TusMiddleware;

class Authenticated implements TusMiddleware
{
    // ...

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, Response $response)
    {
        // Check if user is authenticated
        if (! $this->user->isLoggedIn()) {
            throw new UnauthorizedHttpException('User not authenticated');
        }

        $request->getRequest()->headers->set('Authorization', 'Bearer ' . $this->user->token());
    }

    // ...
}
```

#### Adding a Middleware
To add a middleware, get middleware object from server and simply pass middleware classes.

```php
$server->middleware()->add(Authenticated::class, AnotherMiddleware::class);
```

Or, you can also pass middleware class objects.
```php
$authenticated = new Your\Namespace\Authenticated(new User());

$server->middleware()->add($authenticated);
```

#### Skipping a Middleware
If you wish to skip or ignore any middleware, you can do so by using the `skip` method.

```php
$server->middleware()->skip(Cors::class, AnotherMiddleware::class);
 ```

### Setting up a dev environment and/or running examples locally
An ajax based example for this implementation can be found in `examples/` folder. You can build and run it using docker as described below.

#### Docker
Make sure that [docker](https://docs.docker.com/engine/installation/) and [docker-compose](https://docs.docker.com/compose/install/)
are installed in your system. Then, run docker script from project root.
```shell
# PHP7
$ make dev

# PHP8
$ make dev8

# or, without make

# PHP7
$ bin/docker.sh

# PHP8
$ PHP_VERSION=8 bin/docker.sh
```

Now, the client can be accessed at http://0.0.0.0:8080 and the server can be accessed at http://0.0.0.0:8081. The default API endpoint is set to`/files`
and uploaded files can be found inside `uploads` folder. All docker configs can be found in `docker/` folder.

If you want a fresh start then you can use the following commands. It will delete and recreate all containers, images, and uploads folder.
```shell
# PHP7
$ make dev-fresh

# PHP8
$ make dev8-fresh

# or, without make

# PHP7
$ bin/clean.sh && bin/docker.sh

# PHP8
$ bin/clean.sh && PHP_VERSION=8 bin/docker.sh
```

We also have some utility scripts that will ease your local development experience. See [Makefile](Makefile) for a list of all available commands.
If you are not using [make](https://www.gnu.org/software/make/manual/make.html#Overview), then you can use shell scripts available [here](bin).

### Contributing
1. Install [PHPUnit](https://phpunit.de/) and [composer](https://getcomposer.org/) if you haven't already.
2. Install dependencies
     ```shell
     $ make vendor
     
     # or
     
     $ composer install
     ```
3. Run tests with phpunit
    ```shell
    $ make test
    
    # or
    
    $ composer test
    
    # or
    
    $ ./vendor/bin/phpunit
    ```
4. Validate changes against [PSR2 Coding Standards](http://www.php-fig.org/psr/psr-2/)
    ```shell
    # fix lint issues
    $ make lint
    
    # dry run
    $ make lint-dry
    ```

You can use `xdebug enable` and `xdebug disable` to enable and disable [Xdebug](https://xdebug.org/) inside the container. 

### Questions about this project?
Please feel free to report any bug found. Pull requests, issues, and project recommendations are more than welcome!
