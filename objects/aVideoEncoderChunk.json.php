<?php
// This endpoint receives encoder-produced file chunks and writes them to the temp dir.
// It is a critical write primitive, so it must be authenticated. Two auth methods are
// accepted, both presented by the encoder as HTTP headers (never query strings, so they
// do not leak into access logs):
//
//   1. X-Encoder-Upload-Token  — issued by the site with getToken(ttl, 'EncoderChunkUpload')
//      when the video is queued (see Video::queue()). Validated here with verifyToken(),
//      which needs only $global['salt']/saltV2 and functions.php — NO database connection.
//      This is the common path (site-dispatched uploads) and keeps every chunk PUT off the DB.
//
//   2. X-Encoder-User / X-Encoder-Pass — the streamer credentials the encoder already uses
//      for sendToStreamer. Used as a universal fallback for jobs that carry no token (direct
//      encoder upload, link import). Validated with useVideoHashOrLogin() + User::canUpload(),
//      the same mechanism aVideoEncoder.json.php uses, which does require the full stack.
//
// When a token is present we load config WITHOUT the database (fast path). Only when a token
// is absent do we load the full stack for the credential fallback.
global $global, $doNotConnectDatabaseIncludeConfig, $doNotStartSessionIncludeConfig;

$uploadToken = isset($_SERVER['HTTP_X_ENCODER_UPLOAD_TOKEN']) ? $_SERVER['HTTP_X_ENCODER_UPLOAD_TOKEN'] : '';

if (!empty($uploadToken)) {
    // Token fast path: no DB, no session, no plugins needed to verify it.
    $doNotConnectDatabaseIncludeConfig = 1;
    $doNotStartSessionIncludeConfig = 1;
}
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$authorized = false;
if (!empty($uploadToken)) {
    // Cryptographic token check — no database access.
    $authorized = verifyToken($uploadToken, 'EncoderChunkUpload');
} else {
    // Fallback: authenticate with the streamer credentials (full stack already loaded).
    if (!empty($_SERVER['HTTP_X_ENCODER_USER'])) {
        $_REQUEST['user'] = $_SERVER['HTTP_X_ENCODER_USER'];
        $_REQUEST['pass'] = isset($_SERVER['HTTP_X_ENCODER_PASS']) ? $_SERVER['HTTP_X_ENCODER_PASS'] : '';
        $_REQUEST['encodedPass'] = 1;
    }
    if (function_exists('useVideoHashOrLogin')) {
        useVideoHashOrLogin();
    }
    $authorized = class_exists('User') && User::canUpload();
}

if (!$authorized) {
    http_response_code(403);
    error_log('aVideoEncoderChunk.json.php: rejected unauthorized chunk request from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    die(json_encode(['error' => true, 'msg' => 'Forbidden']));
}

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Security: clean up orphaned chunk files older than 4 hours.
// 4 hours instead of 1 hour because a multi-chunk upload of a large file
// (e.g. 12 GB) can legitimately span several hours on a slow link.
$tmpDir = sys_get_temp_dir();
foreach (glob($tmpDir . DIRECTORY_SEPARATOR . 'YTPChunk_*') as $staleFile) {
    if (is_file($staleFile) && filemtime($staleFile) < time() - 14400) {
        @unlink($staleFile);
    }
}

// Security: enforce a per-request size cap (mirrors PHP's post_max_size; falls back to 4 GB).
// For multi-chunk uploads each request is at most 500 MB, so this limit applies per chunk.
function _parseIniSize(string $val): int
{
    $val  = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    $num  = (int) $val;
    switch ($last) {
        case 'g': $num *= 1024;
        // fall through
        case 'm': $num *= 1024;
        // fall through
        case 'k': $num *= 1024;
    }
    return $num;
}
$rawLimit  = ini_get('post_max_size');
$floorBytes = 4 * 1024 * 1024 * 1024; // 4 GB floor
$maxBytes  = $rawLimit ? max(_parseIniSize($rawLimit), $floorBytes) : $floorBytes;

// Security: absolute cap on the ASSEMBLED file size (sum of every chunk). This bounds how
// much an authorised encoder can write to the temp dir per upload. The default (32 GB) is
// generous enough for a long high-resolution (4K) video; override with
// $global['encoderChunkMaxTotalBytes'] if you need larger.
$maxTotalBytes = !empty($global['encoderChunkMaxTotalBytes'])
    ? (int) $global['encoderChunkMaxTotalBytes']
    : 32 * 1024 * 1024 * 1024; // 32 GB

// Reject obviously oversized requests using the Content-Length hint.
$contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
if ($contentLength > $maxBytes) {
    http_response_code(413);
    error_log("aVideoEncoderChunk.json.php: rejected oversized request ({$contentLength} bytes)");
    die(json_encode(['error' => true, 'msg' => 'Payload too large']));
}

// -----------------------------------------------------------------------
// Multi-chunk assembly mode
//
// The encoder splits large files into 500 MB PUT requests and passes:
//   ?file_id=<16 hex chars>   — unique per upload session
//   &chunk=<0-based index>    — which piece this is
//   &total=<total pieces>     — how many pieces in total
//
// chunk=0 creates/truncates the destination file.
// chunk>0 appends to it.
// After the last chunk the response includes complete=true so the encoder
// knows the assembled file is ready to be registered via sendFile().
// -----------------------------------------------------------------------
$fileId = isset($_GET['file_id']) ? $_GET['file_id'] : '';
if (!empty($fileId)) {
    // Validate file_id to prevent path traversal (only hex chars allowed).
    if (!preg_match('/^[0-9a-f]{1,64}$/i', $fileId)) {
        http_response_code(400);
        error_log("aVideoEncoderChunk.json.php: invalid file_id rejected");
        die(json_encode(['error' => true, 'msg' => 'Invalid file_id']));
    }

    $chunkIndex  = isset($_GET['chunk']) ? (int) $_GET['chunk'] : 0;
    $totalChunks = isset($_GET['total']) ? max(1, (int) $_GET['total']) : 1;
    $destFile    = $tmpDir . DIRECTORY_SEPARATOR . 'YTPChunk_' . $fileId;

    // Bytes already assembled from previous chunks (chunk 0 starts a fresh file).
    $existingBytes = ($chunkIndex === 0 || !file_exists($destFile)) ? 0 : (int) filesize($destFile);

    // Reject early, before reading the body, when the declared size would blow the total cap.
    if (($existingBytes + $contentLength) > $maxTotalBytes) {
        http_response_code(413);
        error_log("aVideoEncoderChunk.json.php: assembled file would exceed {$maxTotalBytes} bytes (existing={$existingBytes}, incoming={$contentLength}), rejecting");
        die(json_encode(['error' => true, 'msg' => 'Payload too large']));
    }

    // chunk 0 → create/truncate; subsequent chunks → append.
    $mode    = ($chunkIndex === 0) ? 'w' : 'a';
    $putdata = fopen('php://input', 'r');
    $fp      = fopen($destFile, $mode);

    $written = 0;
    while (($data = fread($putdata, 1024 * 1024)) !== false && $data !== '') {
        $written += strlen($data);
        if ($written > $maxBytes || ($existingBytes + $written) > $maxTotalBytes) {
            fclose($fp);
            fclose($putdata);
            @unlink($destFile); // drop the partial so the rejected upload does not linger on disk
            http_response_code(413);
            error_log("aVideoEncoderChunk.json.php: stream exceeded limit (chunk={$written}, total=" . ($existingBytes + $written) . "), aborting");
            die(json_encode(['error' => true, 'msg' => 'Payload too large']));
        }
        fwrite($fp, $data);
    }
    fclose($fp);
    fclose($putdata);

    $obj           = new stdClass();
    $obj->file     = $destFile;
    $obj->filesize = filesize($destFile);
    $obj->chunk    = $chunkIndex;
    $obj->total    = $totalChunks;
    $obj->complete = ($chunkIndex + 1 >= $totalChunks);

    error_log("aVideoEncoderChunk.json.php: chunk " . ($chunkIndex + 1) . "/{$totalChunks} written={$written} total_so_far={$obj->filesize} file={$destFile} complete=" . ($obj->complete ? 'yes' : 'no'));
    die(json_encode($obj));
}

// -----------------------------------------------------------------------
// Legacy single-PUT mode (backward compatibility for older encoder builds)
// -----------------------------------------------------------------------
$obj       = new stdClass();
$obj->file = tempnam(sys_get_temp_dir(), 'YTPChunk_');

$putdata = fopen("php://input", "r");
$fp      = fopen($obj->file, "w");

error_log("aVideoEncoderChunk.json.php: start {$obj->file} ");

$written = 0;
while ($data = fread($putdata, 1024 * 1024)) {
    $written += strlen($data);
    if ($written > $maxBytes || $written > $maxTotalBytes) {
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
