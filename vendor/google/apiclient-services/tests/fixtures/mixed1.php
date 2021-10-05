<?php

require_once __DIR__ . '/vendor/autoload.php';

// Mix namespaces in google/apiclient-services with underscores in google/apiclient
$service  = new Google\Service\Books(new Google_Client);
$model    = new Google\Service\Books\Bookshelf();
$resource = new Google\Service\Books\Resource\Bookshelves(
    $service,
    'Books',
    'Resource',
    'Resource'
);

echo "Done!";
