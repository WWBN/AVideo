<?php

namespace BackblazeB2\Tests;

use BackblazeB2\Bucket;
use BackblazeB2\Client;
use BackblazeB2\Exceptions\BadJsonException;
use BackblazeB2\Exceptions\BadValueException;
use BackblazeB2\Exceptions\BucketAlreadyExistsException;
use BackblazeB2\Exceptions\BucketNotEmptyException;
use BackblazeB2\Exceptions\NotFoundException;
use BackblazeB2\Exceptions\ValidationException;
use BackblazeB2\File;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Stream;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    use TestHelper;

    public function testCreatePublicBucket()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'create_bucket_public.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        // Test that we get a public bucket back after creation
        $bucket = $client->createBucket([
            'BucketName' => 'Test bucket',
            'BucketType' => Bucket::TYPE_PUBLIC,
        ]);
        $this->assertInstanceOf(Bucket::class, $bucket);
        $this->assertEquals('Test bucket', $bucket->getName());
        $this->assertEquals(Bucket::TYPE_PUBLIC, $bucket->getType());
    }

    public function testCreatePrivateBucket()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'create_bucket_private.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        // Test that we get a private bucket back after creation
        $bucket = $client->createBucket([
            'BucketName' => 'Test bucket',
            'BucketType' => Bucket::TYPE_PRIVATE,
        ]);
        $this->assertInstanceOf(Bucket::class, $bucket);
        $this->assertEquals('Test bucket', $bucket->getName());
        $this->assertEquals(Bucket::TYPE_PRIVATE, $bucket->getType());
    }

    public function testBucketAlreadyExistsExceptionThrown()
    {
        $this->setExpectedException(BucketAlreadyExistsException::class);

        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(400, [], 'create_bucket_exists.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);
        $client->createBucket([
            'BucketName' => 'I already exist',
            'BucketType' => Bucket::TYPE_PRIVATE,
        ]);
    }

    public function testInvalidBucketTypeThrowsException()
    {
        $this->setExpectedException(ValidationException::class);

        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);
        $client->createBucket([
            'BucketName' => 'Test bucket',
            'BucketType' => 'i am not valid',
        ]);
    }

    public function testUpdateBucketToPrivate()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'update_bucket_to_private.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $bucket = $client->updateBucket([
            'BucketId'   => 'bucketId',
            'BucketType' => Bucket::TYPE_PRIVATE,
        ]);

        $this->assertInstanceOf(Bucket::class, $bucket);
        $this->assertEquals('bucketId', $bucket->getId());
        $this->assertEquals(Bucket::TYPE_PRIVATE, $bucket->getType());
    }

    public function testUpdateBucketToPublic()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'update_bucket_to_public.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $bucket = $client->updateBucket([
            'BucketId'   => 'bucketId',
            'BucketType' => Bucket::TYPE_PUBLIC,
        ]);

        $this->assertInstanceOf(Bucket::class, $bucket);
        $this->assertEquals('bucketId', $bucket->getId());
        $this->assertEquals(Bucket::TYPE_PUBLIC, $bucket->getType());
    }

    public function testList3Buckets()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'list_buckets_3.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $buckets = $client->listBuckets();
        $this->assertInternalType('array', $buckets);
        $this->assertCount(3, $buckets);
        $this->assertInstanceOf(Bucket::class, $buckets[0]);
    }

    public function testEmptyArrayWithNoBuckets()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'list_buckets_0.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $buckets = $client->listBuckets();
        $this->assertInternalType('array', $buckets);
        $this->assertCount(0, $buckets);
    }

    public function testDeleteBucket()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'delete_bucket.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $this->assertTrue($client->deleteBucket([
            'BucketId' => 'bucketId',
        ]));
    }

    public function testBadJsonThrownDeletingNonExistentBucket()
    {
        $this->setExpectedException(BadJsonException::class);

        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(400, [], 'delete_bucket_non_existent.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $client->deleteBucket([
            'BucketId' => 'bucketId',
        ]);
    }

    public function testBucketNotEmptyThrownDeletingNonEmptyBucket()
    {
        $this->setExpectedException(BucketNotEmptyException::class);

        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(400, [], 'bucket_not_empty.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $client->deleteBucket([
            'BucketId' => 'bucketId',
        ]);
    }

    public function testUploadingResource()
    {
        $container = [];
        $history = Middleware::history($container);
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'get_upload_url.json'),
            $this->buildResponseFromStub(200, [], 'upload.json'),
        ], $history);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        // Set up the resource being uploaded.
        $content = 'The quick brown box jumps over the lazy dog';
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, $content);
        rewind($resource);

        $file = $client->upload([
            'BucketId' => 'bucketId',
            'FileName' => 'test.txt',
            'Body'     => $resource,
        ]);

        $this->assertInstanceOf(File::class, $file);

        // We'll also check the Guzzle history to make sure the upload request got created correctly.
        $uploadRequest = $container[2]['request'];
        $this->assertEquals('uploadUrl', $uploadRequest->getRequestTarget());
        $this->assertEquals('authToken', $uploadRequest->getHeader('Authorization')[0]);
        $this->assertEquals(strlen($content), $uploadRequest->getHeader('Content-Length')[0]);
        $this->assertEquals('test.txt', $uploadRequest->getHeader('X-Bz-File-Name')[0]);
        $this->assertEquals(sha1($content), $uploadRequest->getHeader('X-Bz-Content-Sha1')[0]);
        $this->assertEquals(round(microtime(true) * 1000),
            $uploadRequest->getHeader('X-Bz-Info-src_last_modified_millis')[0], '', 100);
        $this->assertInstanceOf(Stream::class, $uploadRequest->getBody());
    }

    public function testUploadingString()
    {
        $container = [];
        $history = Middleware::history($container);
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'get_upload_url.json'),
            $this->buildResponseFromStub(200, [], 'upload.json'),
        ], $history);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $content = 'The quick brown box jumps over the lazy dog';

        $file = $client->upload([
            'BucketId' => 'bucketId',
            'FileName' => 'test.txt',
            'Body'     => $content,
        ]);

        $this->assertInstanceOf(File::class, $file);

        // We'll also check the Guzzle history to make sure the upload request got created correctly.
        $uploadRequest = $container[2]['request'];
        $this->assertEquals('uploadUrl', $uploadRequest->getRequestTarget());
        $this->assertEquals('authToken', $uploadRequest->getHeader('Authorization')[0]);
        $this->assertEquals(strlen($content), $uploadRequest->getHeader('Content-Length')[0]);
        $this->assertEquals('test.txt', $uploadRequest->getHeader('X-Bz-File-Name')[0]);
        $this->assertEquals(sha1($content), $uploadRequest->getHeader('X-Bz-Content-Sha1')[0]);
        $this->assertEquals(round(microtime(true) * 1000),
            $uploadRequest->getHeader('X-Bz-Info-src_last_modified_millis')[0], '', 100);
        $this->assertInstanceOf(Stream::class, $uploadRequest->getBody());
    }

    public function testUploadingWithCustomContentTypeAndLastModified()
    {
        $container = [];
        $history = Middleware::history($container);
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'get_upload_url.json'),
            $this->buildResponseFromStub(200, [], 'upload.json'),
        ], $history);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        // My birthday :)
        $lastModified = 701568000000;
        $contentType = 'text/plain';

        $file = $client->upload([
            'BucketId'         => 'bucketId',
            'FileName'         => 'test.txt',
            'Body'             => 'Test file content',
            'FileContentType'  => $contentType,
            'FileLastModified' => $lastModified,
        ]);

        $this->assertInstanceOf(File::class, $file);

        // We'll also check the Guzzle history to make sure the upload request got created correctly.
        $uploadRequest = $container[2]['request'];
        $this->assertEquals($lastModified, $uploadRequest->getHeader('X-Bz-Info-src_last_modified_millis')[0]);
        $this->assertEquals($contentType, $uploadRequest->getHeader('Content-Type')[0]);
        $this->assertInstanceOf(Stream::class, $uploadRequest->getBody());
    }

    public function testDownloadByIdWithoutSavePath()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'download_content'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $fileContent = $client->download([
            'FileId' => 'fileId',
        ]);

        $this->assertEquals($fileContent, 'The quick brown fox jumps over the lazy dog');
    }

    public function testDownloadByIdWithSavePath()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'download_content'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $client->download([
            'FileId' => 'fileId',
            'SaveAs' => __DIR__.'/test.txt',
        ]);

        $this->assertFileExists(__DIR__.'/test.txt');
        $this->assertEquals('The quick brown fox jumps over the lazy dog', file_get_contents(__DIR__.'/test.txt'));

        unlink(__DIR__.'/test.txt');
    }

    public function testDownloadingByIncorrectIdThrowsException()
    {
        $this->setExpectedException(BadValueException::class);

        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(400, [], 'download_by_incorrect_id.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $client->download([
            'FileId' => 'incorrect',
        ]);
    }

    public function testDownloadByPathWithoutSavePath()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'download_content'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $fileContent = $client->download([
            'BucketName' => 'test-bucket',
            'FileName'   => 'test.txt',
        ]);

        $this->assertEquals($fileContent, 'The quick brown fox jumps over the lazy dog');
    }

    public function testDownloadByPathWithSavePath()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'download_content'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $client->download([
            'BucketName' => 'test-bucket',
            'FileName'   => 'test.txt',
            'SaveAs'     => __DIR__.'/test.txt',
        ]);

        $this->assertFileExists(__DIR__.'/test.txt');
        $this->assertEquals('The quick brown fox jumps over the lazy dog', file_get_contents(__DIR__.'/test.txt'));

        unlink(__DIR__.'/test.txt');
    }

    public function testDownloadingByIncorrectPathThrowsException()
    {
        $this->setExpectedException(NotFoundException::class);

        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(400, [], 'download_by_incorrect_path.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $client->download([
            'BucketName' => 'test-bucket',
            'FileName'   => 'path/to/incorrect/file.txt',
        ]);
    }

    public function testListFilesHandlesMultiplePages()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'list_files_page1.json'),
            $this->buildResponseFromStub(200, [], 'list_files_page2.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $files = $client->listFiles([
            'BucketId' => 'bucketId',
        ]);

        $this->assertInternalType('array', $files);
        $this->assertInstanceOf(File::class, $files[0]);
        $this->assertCount(1500, $files);
    }

    public function testListFilesReturnsEmptyArrayWithNoFiles()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'list_files_empty.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $files = $client->listFiles([
            'BucketId' => 'bucketId',
        ]);

        $this->assertInternalType('array', $files);
        $this->assertCount(0, $files);
    }

    public function testGetFile()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'get_file.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $file = $client->getFile([
            'FileId' => 'fileId',
        ]);

        $this->assertInstanceOf(File::class, $file);
    }

    public function testGettingNonExistentFileThrowsException()
    {
        $this->setExpectedException(BadJsonException::class);

        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(400, [], 'get_file_non_existent.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $client->getFile([
            'FileId' => 'fileId',
        ]);
    }

    public function testDeleteFile()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'get_file.json'),
            $this->buildResponseFromStub(200, [], 'delete_file.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $this->assertTrue($client->deleteFile([
            'FileId' => 'fileId',
        ]));
    }

    public function testDeleteFileRetrievesFileNameWhenNotProvided()
    {
        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(200, [], 'get_file.json'),
            $this->buildResponseFromStub(200, [], 'delete_file.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $this->assertTrue($client->deleteFile([
            'FileId' => 'fileId',
        ]));
    }

    public function testDeletingNonExistentFileThrowsException()
    {
        $this->setExpectedException(BadJsonException::class);

        $guzzle = $this->buildGuzzleFromResponses([
            $this->buildResponseFromStub(200, [], 'authorize_account.json'),
            $this->buildResponseFromStub(400, [], 'delete_file_non_existent.json'),
        ]);

        $client = new Client('testId', 'testKey', ['client' => $guzzle]);

        $this->assertTrue($client->deleteFile([
            'FileId'   => 'fileId',
            'FileName' => 'fileName',
        ]));
    }
}
