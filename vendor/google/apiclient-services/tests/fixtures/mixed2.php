<?php

require_once __DIR__ . '/vendor/autoload.php';

// Mix underscores in google/apiclient-services with namespaces in google/apiclient
$service  = new Google_Service_Books(new Google\Client);
$model    = new Google_Service_Books_Bookshelf();
$resource = new Google_Service_Books_Resource_Bookshelves(
    $service,
    'Books',
    'Resource',
    'Resource'
);

echo "Done!";
