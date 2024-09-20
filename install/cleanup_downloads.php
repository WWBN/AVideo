<?php
require_once __DIR__.'/../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

cleanupDownloadsDirectory();