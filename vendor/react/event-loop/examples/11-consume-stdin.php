<?php

use React\EventLoop\Loop;

require __DIR__ . '/../vendor/autoload.php';

if (!defined('STDIN') || stream_set_blocking(STDIN, false) !== true) {
    fwrite(STDERR, 'ERROR: Unable to set STDIN non-blocking (not CLI or Windows?)' . PHP_EOL);
    exit(1);
}

// read everything from STDIN and report number of bytes
// for illustration purposes only, should use react/stream instead
Loop::addReadStream(STDIN, function ($stream) {
    $chunk = fread($stream, 64 * 1024);

    // reading nothing means we reached EOF
    if ($chunk === '') {
        Loop::removeReadStream($stream);
        stream_set_blocking($stream, true);
        fclose($stream);
        return;
    }

    echo strlen($chunk) . ' bytes' . PHP_EOL;
});
