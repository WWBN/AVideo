<?php

require_once __DIR__ . '/vendor/autoload.php';

// Use namespaces for google/apiclient-services and google/apiclient
$service  = new Google\Service\Books(new Google\Client);
$model    = new Google\Service\Books\Bookshelf();
$resource = new Google\Service\Books\Resource\Bookshelves(
    $service,
    'Books',
    'Resource',
    'Resource'
);

echo "Done!";
