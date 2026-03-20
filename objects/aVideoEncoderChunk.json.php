<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Security: clean up orphaned chunk files older than 1 hour to prevent disk exhaustion.
$tmpDir = sys_get_temp_dir();
foreach (glob($tmpDir . DIRECTORY_SEPARATOR . 'YTPChunk_*') as $staleFile) {
    if (is_file($staleFile) && filemtime($staleFile) < time() - 3600) {
        @unlink($staleFile);
    }
}

// Security: enforce a per-request size cap (mirrors PHP's post_max_size; falls back to 4 GB).
// Legitimate encoder uploads of individual video files fit within this bound.
function _parseIniSize(string $val): int
{
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    $num = (int) $val;
    switch ($last) {
        case 'g': $num *= 1024;
        // fall through
        case 'm': $num *= 1024;
        // fall through
        case 'k': $num *= 1024;
    }
    return $num;
}
$rawLimit = ini_get('post_max_size');
$floorBytes = 4 * 1024 * 1024 * 1024; // 4 GB floor — encoder uploads can be large
$maxBytes = $rawLimit ? max(_parseIniSize($rawLimit), $floorBytes) : $floorBytes;

// Reject obviously oversized requests using the Content-Length hint.
$contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
if ($contentLength > $maxBytes) {
    http_response_code(413);
    error_log("aVideoEncoderChunk.json.php: rejected oversized request ({$contentLength} bytes)");
    die(json_encode(['error' => true, 'msg' => 'Payload too large']));
}

$obj = new stdClass();
$obj->file = tempnam(sys_get_temp_dir(), 'YTPChunk_');

$putdata = fopen("php://input", "r");
$fp = fopen($obj->file, "w");

error_log("aVideoEncoderChunk.json.php: start {$obj->file} ");

$written = 0;
while ($data = fread($putdata, 1024 * 1024)) {
    $written += strlen($data);
    if ($written > $maxBytes) {
        fclose($fp);
        fclose($putdata);
        @unlink($obj->file);
        http_response_code(413);
        error_log("aVideoEncoderChunk.json.php: stream exceeded limit at {$written} bytes, aborting");
        die(json_encode(['error' => true, 'msg' => 'Payload too large']));
    }
    fwrite($fp, $data);
}

fclose($fp);
fclose($putdata);
sleep(1);
$obj->filesize = filesize($obj->file);

$json = json_encode($obj);

error_log("aVideoEncoderChunk.json.php: {$json} ");

die($json);
