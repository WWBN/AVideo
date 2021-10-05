<?php

require_once __DIR__ . '/vendor/autoload.php';

// Use underscores for google/apiclient-services and google/apiclient
$service  = new Google_Service_Books(new Google_Client);
$model    = new Google_Service_Books_Bookshelf();
$resource = new Google_Service_Books_Resource_Bookshelves(
    $service,
    'Books',
    'Resource',
    'Resource'
);

echo "Done!";
