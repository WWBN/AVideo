<?php

use React\EventLoop\Loop;

require __DIR__ . '/../vendor/autoload.php';

// data can be given as first argument or defaults to "y"
$data = (isset($argv[1]) ? $argv[1] : 'y') . "\n";

// repeat data X times in order to fill around 200 KB
$data = str_repeat($data, round(200000 / strlen($data)));

if (!defined('STDOUT') || stream_set_blocking(STDOUT, false) !== true) {
    fwrite(STDERR, 'ERROR: Unable to set STDOUT non-blocking (not CLI or Windows?)' . PHP_EOL);
    exit(1);
}

// write data to STDOUT whenever its write buffer accepts data
// for illustrations purpose only, should use react/stream instead
Loop::addWriteStream(STDOUT, function ($stdout) use (&$data) {
    // try to write data
    $r = fwrite($stdout, $data);

    // nothing could be written despite being writable => closed
    if ($r === 0) {
        Loop::removeWriteStream($stdout);
        fclose($stdout);
        stream_set_blocking($stdout, true);
        fwrite(STDERR, 'Stopped because STDOUT closed' . PHP_EOL);

        return;
    }

    // implement a very simple ring buffer, unless everything has been written at once:
    // everything written in this iteration will be appended for next iteration
    if (isset($data[$r])) {
        $data = substr($data, $r) . substr($data, 0, $r);
    }
});
