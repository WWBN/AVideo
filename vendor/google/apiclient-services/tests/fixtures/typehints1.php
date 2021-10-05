<?php

require_once __DIR__ . '/vendor/autoload.php';

// Test legacy typehints work with new namespaces
class MyTestClass
{
    private $books;

    public function __construct(Google_Service_Books $books)
    {
        $this->books = $books;
    }
}

$testClass = new MyTestClass(new Google\Service\Books(new Google_Client));

echo "Done!";
