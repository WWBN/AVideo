<?php

require_once __DIR__ . '/vendor/autoload.php';

// Test legacy typehints work with new namespaces
class MyTestClass
{
    private $books;

    public function __construct(Google\Service\Books $books)
    {
        $this->books = $books;
    }
}

$testClass = new MyTestClass(new Google_Service_Books(new Google\Client));

echo "Done!";
