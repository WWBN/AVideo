<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * EncoderChunkAssemblerTest
 *
 * Integration-style tests for the resumable/idempotent chunk-upload commit logic used by
 * objects/aVideoEncoderChunk.json.php (extracted into EncoderChunkAssembler so it can be
 * exercised directly, without an HTTP round-trip).
 *
 * Specifically covers the production-safety concerns raised in code review: replaying a
 * chunk (including the LAST one) whose success response was lost must never duplicate
 * bytes in the assembled file, even when the crash happens between appending the chunk
 * and persisting the ".next" resume state.
 *
 * Run with: vendor/bin/phpunit tests/Unit/EncoderChunkAssemblerTest.php
 */
class EncoderChunkAssemblerTest extends TestCase
{
    private $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists('EncoderChunkAssembler')) {
            require_once \APP_ROOT . '/objects/EncoderChunkAssembler.php';
        }

        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ecatest_' . uniqid();
        mkdir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tmpDir . DIRECTORY_SEPARATOR . '*') as $f) {
            @unlink($f);
        }
        @rmdir($this->tmpDir);

        parent::tearDown();
    }

    private function streamFor($bytes)
    {
        $s = fopen('php://memory', 'r+');
        fwrite($s, $bytes);
        rewind($s);
        return $s;
    }

    private function commit($fileId, $chunkIndex, $totalChunks, $bytes)
    {
        return $this->commitWithDeclaredLength($fileId, $chunkIndex, $totalChunks, $bytes, strlen($bytes));
    }

    private function commitWithDeclaredLength($fileId, $chunkIndex, $totalChunks, $bytes, $declaredLength)
    {
        $stream = $this->streamFor($bytes);
        $result = \EncoderChunkAssembler::commitChunk(
            $this->tmpDir,
            $fileId,
            $chunkIndex,
            $totalChunks,
            $stream,
            $declaredLength,
            1024 * 1024 * 1024,
            1024 * 1024 * 1024
        );
        fclose($stream);
        return $result;
    }

    /** @test */
    public function testTwoChunkUploadAssemblesInOrder()
    {
        $fileId = 'aaaa1111';
        $chunk0 = str_repeat('A', 1000);
        $chunk1 = str_repeat('B', 500);

        $r0 = $this->commit($fileId, 0, 2, $chunk0);
        $this->assertSame(\EncoderChunkAssembler::STATUS_OK, $r0->status);
        $this->assertFalse($r0->complete);
        $this->assertSame(1000, $r0->filesize);

        $r1 = $this->commit($fileId, 1, 2, $chunk1);
        $this->assertSame(\EncoderChunkAssembler::STATUS_OK, $r1->status);
        $this->assertTrue($r1->complete);
        $this->assertSame(1500, $r1->filesize);
        $this->assertSame($chunk0 . $chunk1, file_get_contents($r1->file));
    }

    /** @test */
    public function testReplayOfAlreadyCommittedIntermediateChunkDoesNotDuplicateBytes()
    {
        $fileId = 'bbbb2222';
        $chunk0 = str_repeat('A', 1000);
        $chunk1 = str_repeat('B', 500);

        $this->commit($fileId, 0, 3, $chunk0);
        $r1 = $this->commit($fileId, 1, 3, $chunk1);
        $this->assertSame(1500, $r1->filesize);

        // Encoder never saw the success response for chunk 1 and resends it unchanged.
        $replay = $this->commit($fileId, 1, 3, $chunk1);
        $this->assertSame(\EncoderChunkAssembler::STATUS_OK, $replay->status);
        $this->assertTrue($replay->replay);
        $this->assertSame(1500, $replay->filesize);
        $this->assertSame($chunk0 . $chunk1, file_get_contents($replay->file));
    }

    /** @test */
    public function testOutOfOrderChunkIsRejected()
    {
        $fileId = 'cccc3333';
        $chunk1 = str_repeat('B', 500);

        // chunk 0 was never sent/committed for this session.
        $r = $this->commit($fileId, 1, 2, $chunk1);
        $this->assertSame(\EncoderChunkAssembler::STATUS_OUT_OF_ORDER, $r->status);
        $this->assertFileDoesNotExist($this->tmpDir . DIRECTORY_SEPARATOR . 'YTPChunk_' . $fileId);
    }

    /**
     * Simulates the exact crash window the code review flagged: the server appends a
     * chunk's bytes to the assembled file, then dies BEFORE persisting the updated
     * ".next" state. On retry (same chunk resent, because the encoder never received a
     * success response) the commit must NOT duplicate the already-appended bytes.
     * Exercised for the LAST chunk specifically, since that is also the one that must
     * still report complete=true on replay.
     *
     * @test
     */
    public function testCrashBetweenAppendAndStatePersistDoesNotDuplicateLastChunk()
    {
        $fileId = 'dddd4444';
        $chunk0 = str_repeat('A', 1000);
        $chunk1 = str_repeat('B', 500); // final chunk

        $this->commit($fileId, 0, 2, $chunk0);
        $r1 = $this->commit($fileId, 1, 2, $chunk1);
        $this->assertTrue($r1->complete);
        $this->assertSame(1500, $r1->filesize);

        // Simulate "died after append, before state rename": roll the sidecar state back
        // to what it was before chunk 1 was committed, while leaving the assembled file
        // (destFile) exactly as chunk 1's real append left it.
        $stateFile = $this->tmpDir . DIRECTORY_SEPARATOR . 'YTPChunk_' . $fileId . '.next';
        file_put_contents($stateFile, json_encode(['next' => 1, 'size' => 1000]));

        // Encoder times out waiting for the ack and resends the last chunk unchanged.
        $retry = $this->commit($fileId, 1, 2, $chunk1);
        $this->assertSame(\EncoderChunkAssembler::STATUS_OK, $retry->status);
        $this->assertTrue($retry->complete);
        $this->assertSame(1500, $retry->filesize, 'assembled file must not contain a duplicated chunk');
        $this->assertSame($chunk0 . $chunk1, file_get_contents($retry->file));
    }

    /** @test */
    public function testChunkZeroRetryAfterLostResponseIsSafe()
    {
        $fileId = 'eeee5555';
        $chunk0 = str_repeat('A', 1000);

        $this->commit($fileId, 0, 2, $chunk0);
        // Encoder never saw the response for chunk 0, resends it before moving to chunk 1.
        $replay = $this->commit($fileId, 0, 2, $chunk0);
        $this->assertSame(\EncoderChunkAssembler::STATUS_OK, $replay->status);
        $this->assertSame(1000, $replay->filesize);
        $this->assertSame($chunk0, file_get_contents($replay->file));
    }

    /** @test */
    public function testNoScratchFileLeftBehindAfterCommit()
    {
        $fileId = 'ffff6666';
        $this->commit($fileId, 0, 1, str_repeat('A', 100));
        $this->assertFileDoesNotExist($this->tmpDir . DIRECTORY_SEPARATOR . 'YTPChunk_' . $fileId . '.part');
    }

    /**
     * A body that declares Content-Length: 10 but the connection drops after only 3 bytes
     * arrive must be rejected, not silently accepted/completed with the short body.
     *
     * @test
     */
    public function testTruncatedBodyShorterThanDeclaredLengthIsRejected()
    {
        $fileId = 'aabbcc99';
        $r = $this->commitWithDeclaredLength($fileId, 0, 1, 'abc', 10);
        $this->assertSame(\EncoderChunkAssembler::STATUS_SIZE_MISMATCH, $r->status);
        $this->assertFileDoesNotExist($this->tmpDir . DIRECTORY_SEPARATOR . 'YTPChunk_' . $fileId);
        $this->assertFileDoesNotExist($this->tmpDir . DIRECTORY_SEPARATOR . 'YTPChunk_' . $fileId . '.part');
    }
}
