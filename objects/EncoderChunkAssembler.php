<?php

/**
 * Core commit logic for the encoder's resumable chunk upload (used by
 * aVideoEncoderChunk.json.php). Kept as a separate, dependency-free class so it can be
 * exercised directly by unit tests without an HTTP round-trip.
 *
 * A retry always resends the SAME chunk index (the encoder can't tell whether our write
 * succeeded before the ack was lost), so commitChunk() must be idempotent AND crash-safe:
 *   - "<file>.next" is a small JSON sidecar {next, size} tracking, as of the last durably
 *     committed chunk, which chunk we expect next and what the destination size must be.
 *     It is written to a temp file and atomically renamed into place, so it is never
 *     observed half-written.
 *   - Incoming bytes are buffered to a "<file>.part" scratch file first; only once fully
 *     received are they appended to the destination, so a drop mid-chunk never touches
 *     the real file.
 *   - If the process dies AFTER appending to the destination but BEFORE the .next rename
 *     (the one gap an "append then update state" design can't close by itself), the retry
 *     is detected by comparing the destination's actual size against size-before +
 *     size-of-this-chunk: if it already matches, the append is skipped (no duplicate
 *     bytes) and the state is simply (re)persisted.
 *   - The state file is kept even after the last chunk (never deleted here), so a lost
 *     response for the FINAL chunk is still recognized as "already committed" on retry
 *     instead of being treated as out-of-order.
 *   - The actual received byte count is checked against the declared Content-Length (when
 *     known), so a body truncated by a dropped connection is rejected instead of being
 *     committed as if it were the full chunk.
 *   - A per-fileId flock() (a ".lock" sidecar) serializes the whole commit, so two
 *     concurrent attempts for the same upload session can never race each other's writes.
 */
class EncoderChunkAssembler
{
    const STATUS_OK = 'ok';
    const STATUS_OUT_OF_ORDER = 'out_of_order';
    const STATUS_STATE_CORRUPT = 'state_corrupt';
    const STATUS_TOO_LARGE = 'too_large';
    const STATUS_SIZE_MISMATCH = 'size_mismatch';
    const STATUS_COMMIT_FAILED = 'commit_failed';

    /**
     * @param string   $tmpDir        directory to store the assembled file + sidecars in
     * @param string   $fileId        session id (caller must validate its format)
     * @param int      $chunkIndex    0-based index of the chunk in this request
     * @param int      $totalChunks   total number of chunks in the session
     * @param resource $inputStream   readable stream with the chunk's raw bytes
     * @param int      $contentLength declared size of this request (0 if unknown)
     * @param int      $maxBytes      per-chunk byte cap
     * @param int      $maxTotalBytes cumulative assembled-file byte cap
     * @return stdClass ->status is one of the STATUS_* constants; on STATUS_OK,
     *                   ->file/->filesize/->chunk/->total/->complete/->replay are set;
     *                   otherwise ->msg holds a human-readable reason.
     */
    public static function commitChunk($tmpDir, $fileId, $chunkIndex, $totalChunks, $inputStream, $contentLength, $maxBytes, $maxTotalBytes)
    {
        $destFile    = $tmpDir . DIRECTORY_SEPARATOR . 'YTPChunk_' . $fileId;
        $stateFile   = $destFile . '.next';
        $scratchFile = $destFile . '.part';
        $lockFile    = $destFile . '.lock';

        // Serialize concurrent attempts for the same fileId (e.g. an overlapping client-side
        // retry) so two requests can never both read the same "before" state and race each
        // other's writes to .part/.next/the final file.
        $lockFp = fopen($lockFile, 'c');
        flock($lockFp, LOCK_EX);
        try {
            return self::doCommitChunk($destFile, $stateFile, $scratchFile, $chunkIndex, $totalChunks, $inputStream, $contentLength, $maxBytes, $maxTotalBytes);
        } finally {
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
        }
    }

    private static function doCommitChunk($destFile, $stateFile, $scratchFile, $chunkIndex, $totalChunks, $inputStream, $contentLength, $maxBytes, $maxTotalBytes)
    {
        // A prior chunk in this same session may have just been written by this same PHP
        // process (e.g. tests, or a long-lived worker) — never trust a stale stat cache here.
        clearstatcache(true, $destFile);
        clearstatcache(true, $stateFile);

        $result = new stdClass();

        // chunk 0 always (re)starts the session — ignore any stale state from a previous run.
        if ($chunkIndex === 0 || !file_exists($stateFile)) {
            $state = ['next' => 0, 'size' => 0];
        } else {
            $state = json_decode(file_get_contents($stateFile), true);
            if (!is_array($state) || !isset($state['next'], $state['size'])) {
                $result->status = self::STATUS_STATE_CORRUPT;
                $result->msg = 'Upload session state is corrupt, restart the upload';
                return $result;
            }
        }
        $nextExpectedChunk = (int) $state['next'];
        $expectedPreSize   = (int) $state['size'];

        if ($chunkIndex < $nextExpectedChunk) {
            // Already committed by a previous attempt whose success response never arrived.
            // Confirm the current state instead of re-appending (which would duplicate bytes).
            $result->status   = self::STATUS_OK;
            $result->file     = $destFile;
            $result->filesize = file_exists($destFile) ? filesize($destFile) : 0;
            $result->chunk    = $chunkIndex;
            $result->total    = $totalChunks;
            $result->complete = ($nextExpectedChunk >= $totalChunks);
            $result->replay   = true;
            return $result;
        }

        if ($chunkIndex > $nextExpectedChunk) {
            // A gap: $nextExpectedChunk is still missing. Accepting this would corrupt the
            // assembly (wrong offset), so reject and let the encoder retry the right chunk.
            $result->status = self::STATUS_OUT_OF_ORDER;
            $result->msg = "Expected chunk {$nextExpectedChunk}, got {$chunkIndex}";
            return $result;
        }

        // Reject early, before reading the body, when the declared size would blow the total cap.
        if (($expectedPreSize + $contentLength) > $maxTotalBytes) {
            $result->status = self::STATUS_TOO_LARGE;
            $result->msg = 'Payload too large';
            return $result;
        }

        // Buffer the incoming chunk to a scratch file first; only once it is fully received
        // do we commit it into $destFile, so a mid-stream failure never touches the real file.
        $fp = fopen($scratchFile, 'w');
        $written = 0;
        while (($data = fread($inputStream, 1024 * 1024)) !== false && $data !== '') {
            $written += strlen($data);
            if ($written > $maxBytes || ($expectedPreSize + $written) > $maxTotalBytes) {
                fclose($fp);
                @unlink($scratchFile);
                $result->status = self::STATUS_TOO_LARGE;
                $result->msg = 'Payload too large';
                return $result;
            }
            fwrite($fp, $data);
        }
        fclose($fp);

        // A client that declares Content-Length up front but the connection drops before all
        // of it arrives must not be silently accepted as a complete, valid chunk.
        if ($contentLength > 0 && $written !== $contentLength) {
            @unlink($scratchFile);
            $result->status = self::STATUS_SIZE_MISMATCH;
            $result->msg = "Declared size {$contentLength} does not match received size {$written}";
            return $result;
        }

        // Validate we actually buffered what we counted (catches a truncated/failed scratch write).
        clearstatcache(true, $scratchFile);
        $scratchSize = file_exists($scratchFile) ? filesize($scratchFile) : -1;
        if ($scratchSize !== $written) {
            @unlink($scratchFile);
            $result->status = self::STATUS_COMMIT_FAILED;
            $result->msg = 'Failed to buffer chunk';
            return $result;
        }

        $actualDestSize = file_exists($destFile) ? filesize($destFile) : 0;
        if ($chunkIndex !== 0 && $actualDestSize === $expectedPreSize + $written) {
            // The append for this exact chunk already happened (the process died AFTER
            // writing it but BEFORE persisting the new state) — do not re-append, just
            // finish the interrupted commit by (re)persisting the state below.
            $appended = false;
            $newSize  = $actualDestSize;
        } elseif ($actualDestSize === $expectedPreSize) {
            // Normal case: append not yet applied.
            $appended = true;
        } elseif ($chunkIndex === 0) {
            // chunk 0 always truncates, so any pre-existing content at this path is
            // irrelevant/safe to discard regardless of its size.
            $appended = true;
        } else {
            // Destination size matches neither "before this chunk" nor "after this chunk":
            // state is inconsistent with reality. Refuse to guess and let the caller restart.
            @unlink($scratchFile);
            $result->status = self::STATUS_STATE_CORRUPT;
            $result->msg = 'Upload session state is inconsistent, restart the upload';
            return $result;
        }

        if ($appended) {
            // chunk 0 creates/truncates the destination, later chunks append.
            $mode = ($chunkIndex === 0) ? 'w' : 'a';
            $src  = fopen($scratchFile, 'r');
            $dst  = fopen($destFile, $mode);
            $copied = stream_copy_to_stream($src, $dst);
            fclose($src);
            fclose($dst);
            clearstatcache(true, $destFile);
            $newSize = ($chunkIndex === 0) ? $written : $expectedPreSize + $written;
            if ($copied !== $written || filesize($destFile) !== $newSize) {
                @unlink($scratchFile);
                $result->status = self::STATUS_COMMIT_FAILED;
                $result->msg = 'Failed to commit chunk';
                return $result;
            }
        }
        @unlink($scratchFile);

        // Persist the new state atomically (write to a temp file, then rename into place) so
        // a crash mid-write never leaves a half-written/corrupt .next file.
        $nextExpectedChunk = $chunkIndex + 1;
        $tmpStateFile = $stateFile . '.tmp_' . getmypid() . '_' . mt_rand();
        file_put_contents($tmpStateFile, json_encode(['next' => $nextExpectedChunk, 'size' => $newSize]), LOCK_EX);
        rename($tmpStateFile, $stateFile);

        $complete = ($nextExpectedChunk >= $totalChunks);
        // Deliberately NOT deleting $stateFile on completion: a lost response for the LAST
        // chunk must still be recognized as "already committed" on retry (see the
        // $chunkIndex < $nextExpectedChunk branch above).

        $result->status   = self::STATUS_OK;
        $result->file     = $destFile;
        $result->filesize = $newSize;
        $result->chunk    = $chunkIndex;
        $result->total    = $totalChunks;
        $result->complete = $complete;
        $result->replay   = false;
        return $result;
    }
}
